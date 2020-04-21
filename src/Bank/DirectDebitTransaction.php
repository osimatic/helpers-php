<?php

namespace Osimatic\Helpers\Bank;

class DirectDebitTransaction
{
	/**
	 * Retourne le jour du mois suivant la date de facture
	 * @param \DateTime $invoiceDate
	 * @param int $transactionDay
	 * @return \DateTime
	 */
	public static function getTransactionDate(\DateTime $invoiceDate, int $transactionDay=20): \DateTime
	{
		$timestampNextMonth = $invoiceDate->getTimestamp() + (20*24*3600);
		$transactionTimestamp = mktime(0, 0, 0, date('m', $timestampNextMonth), $transactionDay, date('Y', $timestampNextMonth));
		try {
			return new \DateTime('@'.$transactionTimestamp);
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * @param $year
	 * @param $month
	 * @param int $transactionDay
	 * @return \DateTime
	 */
	public static function getTransactionDateByMonth($year, $month, int $transactionDay=20): \DateTime
	{
		try {
			return self::getTransactionDate(new \DateTime($year.'-'.sprintf('%02d', $month).'-01 00:00:00'), $transactionDay);
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * @param string $filePath
	 * @param array $creditorData
	 * @param string $referenceTransactions
	 * @param array $listTransactions
	 * @param \DateTime $transactionDate
	 * @return bool
	 */
	public static function getTransactionListXmlFile(string $filePath, array $creditorData, string $referenceTransactions, array $listTransactions, \DateTime $transactionDate): bool
	{
		if (empty($listTransactions)) {
			return false;
		}

		$nbTransactions = count($listTransactions);
		foreach ($listTransactions as $numTransaction => $transaction) {
			$listTransactions[$numTransaction]['amount'] = round($transaction['amount'], 2);
		}

		$totalAmount = 0;
		foreach ($listTransactions as $numTransaction => $transaction) {
			$totalAmount += $transaction['amount'];
		}

		$creancierIcs = $creditorData['ics'] ?? null;
		$creancierIban = $creditorData['iban'] ?? null;
		$creancierBic = $creditorData['bic'] ?? null;

		$xml = [
			'CstmrDrctDbtInitn' => [
				'GrpHdr' => [
					'MsgId' => $referenceTransactions, // Référence du message qui n'est pas utilisée comme référence fonctionnelle.
					'CreDtTm' => date('Y-m-d\TH:i:s'),
					'NbOfTxs' => $nbTransactions,
					'CtrlSum' => $totalAmount,
					'InitgPty' => [
						'Nm' => 'Osimatic',
					],
				],
				'PmtInf' => [
					'PmtInfId' => $referenceTransactions, // Référence du lot. Elle est restituée sur le relevé de compte du créancier en cas de comptabilisation par lot.
					'PmtMtd' => 'DD',
					'BtchBookg' => 'false',
					'NbOfTxs' => $nbTransactions,
					'CtrlSum' => $totalAmount,
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
						'Nm' => 'Osimatic',
					],
					'CdtrAcct' => [
						'Id' => [
							'IBAN' => $creancierIban,
						]
					],
					'CdtrAgt' => [
						'FinInstnId' => [
							'BIC' => $creancierBic,
						]
					],
					'ChrgBr' => 'SLEV',
					'CdtrSchmeId' => [
						'Id' => [
							'PrvtId' => [
								'Othr' => [
									'Id' => $creancierIcs,
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
					'EndToEndId' => $transaction['invoice_ref'], // Référence de bout-en-bout qui est restituée au débiteur
				],
				'InstdAmt' => [
					'@attributes' => [
						'Ccy' => 'EUR'
					],
					'@value' => $transaction['amount']
				],
				'DrctDbtTx' => [
					'MndtRltdInf' => [
						'MndtId' => $transaction['sepa_mandate_rum'],
						'DtOfSgntr' => date('Y-m-d', strtotime($transaction['sepa_mandate_date_of_signature'])),
					]
				],
				'DbtrAgt' => [
					'FinInstnId' => [
						'BIC' => $transaction['debtor_bic'],
					]
				],
				'Dbtr' => [
					'Nm' => utf8_encode($transaction['debtor_identity']),
				],
				'DbtrAcct' => [
					'Id' => [
						'IBAN' => $transaction['debtor_iban'],
					]
				],
				'RmtInf' => [
					'Ustrd' => $transaction['invoice_number'],
				]
			];
		}

		\Osimatic\Helpers\Text\XML::generateFile($filePath, $xml, 'Document', 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 H:/Desktop/pain.008.001.02.xsd"');

		return true;
	}
}