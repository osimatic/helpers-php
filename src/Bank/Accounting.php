<?php

namespace Osimatic\Helpers\Bank;

use Osimatic\Helpers\Location\Country;

/**
 * Class Accounting
 * @package Osimatic\Helpers\Bank
 */
class Accounting
{
	/**
	 *
	 */
	protected $paymentMethod;

	/**
	 *
	 */
	protected $accounts;

	/**
	 *
	 */
	protected $transactionList;

	/**
	 * @param string|null $paymentMethod
	 * @return self
	 */
	public function setPaymentMethod(?string $paymentMethod): self
	{
		$this->paymentMethod = $paymentMethod;

		return $this;
	}

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
	 * @param array $transactionList
	 * @return self
	 */
	public function setTransactionList(array $transactionList): self
	{
		$this->transactionList = $transactionList;

		return $this;
	}

	public function save($filePath)
	{
		// Header
		$tableHead = [
			'DATES',
			'JOURNAL',
			'COMPTES',
			'num facture',
			'NOMS',
			'DEBIT (ttc)',
			'Crédit (ht et tva)',
			'code monnaie',
		];

		// Liste des transactions
		$tableBody = [];
		foreach ($this->transactionList as $transaction) {
			$codeMonnaie = $this->getCurrencyCode($transaction['currency'] ?? 'EUR');
			$accountkey = $this->getAccountKey($transaction['customer_country'], $transaction['customer_zip_code']);
			$addTvaLine = BillingTax::getBillingTaxRate($transaction['customer_country'], $transaction['customer_zip_code'], $transaction['customer_vat_number'], 'FR') != 0;

			$transaction['amount_incl_tax'] = round($transaction['amount_incl_tax'], 2);
			$transaction['amount_excl_tax'] = round($transaction['amount_excl_tax'], 2);
			$transaction['amount_vat'] = round($transaction['amount_vat'], 2);

			$transactionDate = null;
			try {
				$transactionDate = new \DateTime($transaction['sql_date'].' 00:00:00');
			}
			catch (\Exception $e) {}

			// Ligne débit
			$account = $this->accounts[$accountkey]['debit'] ?? '';
			$tableBody[] = [
				$transactionDate->format('d/m/Y'),
				'VT',
				$account,
				$transaction['invoice_number'],
				$transaction['customer_identity'],
				$transaction['amount_incl_tax'],
				0,
				$codeMonnaie,
			];

			// Ligne crédit (HT)
			$account = $this->accounts[$accountkey]['credit_excl_tax'] ?? '';
			$tableBody[] = [
				$transactionDate->format('d/m/Y'),
				'VT',
				$account,
				$transaction['invoice_number'],
				$transaction['customer_identity'],
				0,
				$transaction['amount_excl_tax'],
				$codeMonnaie,
			];

			// Ligne crédit (TVA)
			if ($addTvaLine) {
				$account = $this->accounts[$accountkey]['credit_vat'] ?? '';
				$tableBody[] = [
					$transactionDate->format('d/m/Y'),
					'VT',
					$account,
					$transaction['invoice_number'],
					$transaction['customer_identity'],
					0,
					$transaction['amount_vat'],
					$codeMonnaie,
				];
			}
		}

		\Osimatic\Helpers\Text\CSV::generateFile($filePath, $tableHead, $tableBody, null);
	}

	private function getAccountKey(string $customerCountry, string $customerZipCode): string
	{
		if (Country::isCountryInFranceOverseas($customerCountry, $customerZipCode)) {
			return 'france_dom';
		}
		if ('FR' === $customerCountry) {
			return 'france';
		}
		if (Country::isCountryInEuropeanUnion($customerCountry)) {
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