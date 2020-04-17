<?php

namespace Osimatic\Helpers\Bank;

class DirectDebitTransaction
{
	/**
	 * Retourne le jour du mois suivant la date de facture
	 * @param string $invoiceSqlDate
	 * @param int $transactionDay
	 * @return string
	 */
	public static function getTransactionSqlDate(string $invoiceSqlDate, int $transactionDay=20): string
	{
		$timestampNextMonth = strtotime($invoiceSqlDate.' 00:00:00') + (20*24*3600);
		$transactionTimestamp = mktime(0, 0, 0, date('m', $timestampNextMonth), $transactionDay, date('Y', $timestampNextMonth));
		return date('Y-m-d', $transactionTimestamp);
	}

	/**
	 * @param string $invoiceSqlDate
	 * @param int $transactionDay
	 * @return string
	 */
	public static function getTransactionSqlDateTime(string $invoiceSqlDate, int $transactionDay=20): string
	{
		return self::getTransactionSqlDate($invoiceSqlDate, $transactionDay).' 00:00:00';
	}

	/**
	 * @param $month
	 * @param $year
	 * @param int $transactionDay
	 * @return string
	 */
	public static function getTransactionSqlDateByMonth($month, $year, int $transactionDay=20): string
	{
		return self::getTransactionSqlDate($year.'-'.sprintf('%02d', $month).'-01', $transactionDay);
	}

	/**
	 * @param array $creditorData
	 * @param string $referenceTransactions
	 * @param array $listTransactions
	 * @param string $sqlDateTransaction
	 * @return string|null
	 */
	public static function getXmlContentForTransactionsList(array $creditorData, string $referenceTransactions, array $listTransactions, string $sqlDateTransaction): ?string
	{
		if (empty($listTransactions)) {
			return null;
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
					'ReqdColltnDt' => $sqlDateTransaction,
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

		$xmlStr = \Osimatic\Helpers\Text\XML::convertArrayToXml($xml, 'Document');
		$xmlStr = str_replace('<Document>', '<Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 H:/Desktop/pain.008.001.02.xsd">', $xmlStr);
		return $xmlStr;
	}
}