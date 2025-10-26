<?php

namespace Osimatic\Bank;

class DirectDebitTransaction
{
	/**
	 * Retourne le jour du mois suivant la date de facture
	 * @param \DateTime $invoiceDate
	 * @param int $transactionDay
	 * @return \DateTime
	 */
	public static function getTransactionDate(\DateTime $invoiceDate, int $transactionDay=10): \DateTime
	{
		$transactionDate = clone $invoiceDate;
		$transactionDate->modify('+1 month');
		$transactionDate->setDate($transactionDate->format('Y'), $transactionDate->format('m'), $transactionDay);
		return $transactionDate;

		/*$transactionTimestamp = mktime(0, 0, 0, date('m', $timestampNextMonth), $transactionDay, date('Y', $timestampNextMonth));
		try {
			return (new \DateTime('@'.$transactionTimestamp))->setTimezone($invoiceDate->getTimezone());
		}
		catch (\Exception $e) { }
		return null;*/
	}

	/**
	 * Retourne le jour du mois suivant le mois passé en paramètre
	 * @param int $year
	 * @param int $month
	 * @param int $transactionDay
	 * @return \DateTime|null
	 */
	public static function getTransactionDateByMonth(int $year, int $month, int $transactionDay=10): ?\DateTime
	{
		try {
			return self::getTransactionDate(new \DateTime($year.'-'.sprintf('%02d', $month).'-01 00:00:00'), $transactionDay);
		}
		catch (\Exception) {}
		return null;
	}

	/**
	 * @param string $filePath
	 * @param BankAccountInterface $creditorBankAccount
	 * @param string $sepaCreditorIdentifier
	 * @param DirectDebitTransactionInterface[] $listTransactions
	 * @param string|null $referenceTransactions
	 * @param \DateTime|null $transactionDate
	 * @return bool
	 */
	public static function getTransactionsListXmlFile(string $filePath, BankAccountInterface $creditorBankAccount, string $sepaCreditorIdentifier, array $listTransactions, ?string $referenceTransactions=null, ?\DateTime $transactionDate=null): bool
	{
		if (empty($listTransactions)) {
			return false;
		}

		if (null === $transactionDate) {
			$transactionDate = self::getTransactionDateByMonth(date('Y'), date('m'));
		}

		// si date passée, le prélèvement est effectué le lendemain.
		if ($transactionDate->format('Y-m-d') < date('Y-m-d')) {
			$transactionDate = new \DateTime();
			$transactionDate->modify('+1 day');
		}

		if (null === $referenceTransactions) {
			$referenceTransactions = 'SEPA'.date('YmdH:i:s');
		}

		// On arrondit les montants à 2 chiffres après la virgule.
		foreach ($listTransactions as $transaction) {
			$transaction->setAmount(round($transaction->getAmount(), 2));
		}

		$nbTransactions = count($listTransactions);
		$totalAmount = array_sum(array_map(static fn(DirectDebitTransactionInterface $transaction) => $transaction->getAmount(), $listTransactions));

		$xml = [
			'CstmrDrctDbtInitn' => [
				'GrpHdr' => [
					'MsgId' => $referenceTransactions, // Référence du message qui n'est pas utilisée comme référence fonctionnelle.
					'CreDtTm' => date('Y-m-d\TH:i:s'),
					'NbOfTxs' => $nbTransactions,
					'CtrlSum' => self::formatAmount($totalAmount),
					'InitgPty' => [
						'Nm' => $creditorBankAccount->getIdentity(),
					],
				],
				'PmtInf' => [
					'PmtInfId' => $referenceTransactions, // Référence du lot. Elle est restituée sur le relevé de compte du créancier en cas de comptabilisation par lot.
					'PmtMtd' => 'DD',
					'BtchBookg' => 'false',
					'NbOfTxs' => $nbTransactions,
					'CtrlSum' => self::formatAmount($totalAmount),
					'PmtTpInf' => [
						'SvcLvl' => [
							'Cd' => 'SEPA',
						],
						'LclInstrm' => [
							'Cd' => 'CORE',
						],
						'SeqTp' => 'RCUR',
					],
					'ReqdColltnDt' => $transactionDate->format('Y-m-d'),
					'Cdtr' => [
						'Nm' => $creditorBankAccount->getIdentity(),
					],
					'CdtrAcct' => [
						'Id' => [
							'IBAN' => $creditorBankAccount->getIban(),
						]
					],
					'CdtrAgt' => [
						'FinInstnId' => [
							'BIC' => $creditorBankAccount->getBic(),
						]
					],
					'ChrgBr' => 'SLEV',
					'CdtrSchmeId' => [
						'Id' => [
							'PrvtId' => [
								'Othr' => [
									'Id' => $sepaCreditorIdentifier,
									'SchmeNm' => [
										'Prtry' => 'SEPA',
									]
								]
							]
						]
					]
				],
			]
		];

		$xml['CstmrDrctDbtInitn']['PmtInf']['DrctDbtTxInf'] = [];
		foreach ($listTransactions as $transaction) {
			$xml['CstmrDrctDbtInitn']['PmtInf']['DrctDbtTxInf'][] = [
				'PmtId' => [
					// 'InstrId' => , // Référence de l'opération
					'EndToEndId' => $transaction->getInvoiceReference(), // Référence de bout-en-bout qui est restituée au débiteur
				],
				'InstdAmt' => [
					'@attributes' => [
						'Ccy' => $transaction->getCurrency()
					],
					'@value' => self::formatAmount($transaction->getAmount())
				],
				'DrctDbtTx' => [
					'MndtRltdInf' => [
						'MndtId' => $transaction->getSepaMandateRum(),
						'DtOfSgntr' => $transaction->getSepaMandateDateOfSignature()->format('Y-m-d'),
					]
				],
				'DbtrAgt' => [
					'FinInstnId' => [
						'BIC' => $transaction->getDebtorBankAccount()->getBic(),
					]
				],
				'Dbtr' => [
					'Nm' => $transaction->getDebtorBankAccount()->getIdentity(),
				],
				'DbtrAcct' => [
					'Id' => [
						'IBAN' => $transaction->getDebtorBankAccount()->getIban(),
					]
				],
				'RmtInf' => [
					'Ustrd' => $transaction->getInvoiceNumber(),
				]
			];
		}

		$xmlGenerator = new \Osimatic\Text\XMLGenerator();
		$xmlGenerator->generateFile($filePath, $xml, 'Document', 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 H:/Desktop/pain.008.001.02.xsd"');

		return true;
	}

	/**
	 * @param float $amount
	 * @return string
	 */
	private static function formatAmount(float $amount): string
	{
		return number_format($amount, 2, '.', '');
	}

}