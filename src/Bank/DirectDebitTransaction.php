<?php

namespace Osimatic\Bank;

/**
 * Utility class for SEPA direct debit transaction operations
 * Provides methods for transaction date calculation and XML file generation for SEPA direct debit batches
 */
class DirectDebitTransaction
{
	/**
	 * Calculate the transaction date for a direct debit based on invoice date
	 * Returns a date in the next month after the invoice date, on the specified day
	 * For example, invoice on 2024-01-15 with transactionDay=10 returns 2024-02-10
	 * @param \DateTime $invoiceDate The invoice creation date
	 * @param int $transactionDay The day of the month for the transaction (default: 10)
	 * @return \DateTime The calculated transaction date
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
	 * Calculate the transaction date for the month following the specified year and month
	 * Creates a date on the 1st of the specified month, then calculates transaction date for next month
	 * For example, getTransactionDateByMonth(2024, 1, 10) returns 2024-02-10
	 * @param int $year The year
	 * @param int $month The month (1-12)
	 * @param int $transactionDay The day of the month for the transaction (default: 10)
	 * @return \DateTime|null The calculated transaction date, or null if date creation fails
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
	 * Generate SEPA direct debit XML file (pain.008.001.02 format)
	 * Creates an XML file for batch processing of direct debit transactions according to SEPA standards
	 * The file can be uploaded to the bank for automated direct debit processing
	 * @param string $filePath Path where the XML file will be saved
	 * @param BankAccountInterface $creditorBankAccount The creditor's (merchant's) bank account information
	 * @param string $sepaCreditorIdentifier The SEPA creditor identifier (ICS - Identifiant Créancier SEPA)
	 * @param DirectDebitTransactionInterface[] $listTransactions Array of transactions to include in the batch
	 * @param string|null $referenceTransactions Optional batch reference (auto-generated if null)
	 * @param \DateTime|null $transactionDate Optional transaction execution date (calculated if null)
	 * @return bool True if file generation succeeded, false if transaction list is empty
	 */
	public static function getTransactionsListXmlFile(string $filePath, BankAccountInterface $creditorBankAccount, string $sepaCreditorIdentifier, array $listTransactions, ?string $referenceTransactions=null, ?\DateTime $transactionDate=null): bool
	{
		if (empty($listTransactions)) {
			return false;
		}

		if (null === $transactionDate) {
			$transactionDate = self::getTransactionDateByMonth(date('Y'), date('m'));
		}

		// If date is in the past, schedule the direct debit for tomorrow
		if ($transactionDate->format('Y-m-d') < date('Y-m-d')) {
			$transactionDate = new \DateTime();
			$transactionDate->modify('+1 day');
		}

		if (null === $referenceTransactions) {
			$referenceTransactions = 'SEPA'.date('YmdH:i:s');
		}

		// Round amounts to 2 decimal places
		foreach ($listTransactions as $transaction) {
			$transaction->setAmount(round($transaction->getAmount(), 2));
		}

		$nbTransactions = count($listTransactions);
		$totalAmount = array_sum(array_map(static fn(DirectDebitTransactionInterface $transaction) => $transaction->getAmount(), $listTransactions));

		$xml = [
			'CstmrDrctDbtInitn' => [
				'GrpHdr' => [
					'MsgId' => $referenceTransactions, // Message reference (not used as functional reference)
					'CreDtTm' => date('Y-m-d\TH:i:s'),
					'NbOfTxs' => $nbTransactions,
					'CtrlSum' => self::formatAmount($totalAmount),
					'InitgPty' => [
						'Nm' => $creditorBankAccount->getIdentity(),
					],
				],
				'PmtInf' => [
					'PmtInfId' => $referenceTransactions, // Batch reference. Returned on the creditor's bank statement when batch accounting is used
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
					// 'InstrId' => , // Operation reference
					'EndToEndId' => $transaction->getInvoiceReference(), // End-to-end reference returned to the debtor
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
	 * Format amount for SEPA XML file
	 * Formats the amount with exactly 2 decimal places using a dot as decimal separator
	 * @param float $amount The amount to format
	 * @return string The formatted amount (e.g., "10.00")
	 */
	private static function formatAmount(float $amount): string
	{
		return number_format($amount, 2, '.', '');
	}

}