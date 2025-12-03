<?php

namespace Osimatic\Bank;

use Osimatic\Location\Country;
use Osimatic\Text\CSVGenerator;

/**
 * Class Accounting
 * @package Osimatic\Bank
 */
class Accounting
{
	/**
	 *
	 */
	protected array $accounts = [];

	/**
	 * @param array $accounts
	 * @return self
	 */
	public function setAccounts(array $accounts): self
	{
		$this->accounts = $accounts;

		return $this;
	}

	/**
	 * @param string $filePath
	 * @param AccountingTransaction[] $transactionList
	 */
	public function generateFile(string $filePath, array $transactionList): void
	{
		// Header
		$tableHead = [
			'DATES',
			'JOURNAL',
			'COMPTES',
			'NUM FACTURE',
			'NOMS',
			'DEBIT (ttc)',
			'CREDIT (ht et tva)',
			'CODE MONNAIE',
		];

		// Liste des transactions
		$tableBody = [];
		foreach ($transactionList as $transaction) {
			$codeMonnaie = $this->getCurrencyCode($transaction->getCurrency());
			$accountKey = $this->getAccountKey($transaction->getCustomerCountry(), $transaction->getCustomerPostCode(), $transaction->getCustomerVatNumber());
			$addTvaLine = !empty(BillingTax::getBillingTaxRate($transaction->getCustomerCountry(), $transaction->getCustomerPostCode(), $transaction->getCustomerVatNumber(), 'FR'));

			$transactionDate = $transaction->getDateTime();

			// Ligne débit
			$account = $transaction->getDebitAccount() ?? $this->accounts[$accountKey]['debit'] ?? '';
			$tableBody[] = [
				$transactionDate->format('d/m/Y'),
				'VT',
				$account,
				$transaction->getInvoiceNumber(),
				$transaction->getCustomerIdentity(),
				$transaction->getAmountInclTax(),
				0,
				$codeMonnaie,
			];

			// Ligne crédit (HT)
			$account = $this->accounts[$accountKey]['credit_excl_tax'] ?? '';
			$tableBody[] = [
				$transactionDate->format('d/m/Y'),
				'VT',
				$account,
				$transaction->getInvoiceNumber(),
				$transaction->getCustomerIdentity(),
				0,
				$transaction->getAmountExclTax(),
				$codeMonnaie,
			];

			// Ligne crédit (TVA)
			if ($addTvaLine) {
				$account = $this->accounts[$accountKey]['credit_vat'] ?? '';
				$tableBody[] = [
					$transactionDate->format('d/m/Y'),
					'VT',
					$account,
					$transaction->getInvoiceNumber(),
					$transaction->getCustomerIdentity(),
					0,
					$transaction->getAmountVat(),
					$codeMonnaie,
				];
			}
		}

		(new CSVGenerator())->generateFile($filePath, (new \Osimatic\Data\Table($tableHead, $tableBody, []))->getTableData());
	}

	private function getAccountKey(?string $customerCountry, ?string $customerZipCode=null, ?string $vatNumber=null): string
	{
		if (Country::isCountryInFranceOverseas($customerCountry, $customerZipCode)) {
			return 'france_dom';
		}
		if ('FR' === $customerCountry) {
			return 'france';
		}
		if (Country::isCountryInEuropeanUnion($customerCountry)) {
			if (empty($vatNumber)) { // Si n° TVA non renseigné, vente assimilée à la France (TVA = 20%)
				return 'france';
			}
			return 'inside_ue';
		}
		return 'outside_ue';
	}

	private function getCurrencyCode(string $currency): string
	{
		if ('GBP' === $currency) {
			return 'G';
		}
		return 'E';
	}

}