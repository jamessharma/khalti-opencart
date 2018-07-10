<?php
class ModelExtensionPaymentKhalti extends Model {

	public function install() {
	}

	public function uninstall() {
	}

	public function getOrder($order_id) {
	}

	public function addRefundRecord($order, $result) {
		
	}

	public function capture($order_id, $capture_amount, $currency) {
		
	}

	public function updateCaptureStatus($khalti_order_id, $status) {
		
	}

	public function updateTransactionId($khalti_order_id, $transaction_id) {
		
	}

	public function void($order_id) {
	}

	public function updateVoidStatus($khalti_order_id, $status) {

	}

	public function refund($order_id, $refund_amount) {
		
	}

	public function updateRefundStatus($khalti_order_id, $status) {
		
	}

	public function sendCurl($url, $data) {
		
	}

	private function getTransactions($khalti_order_id) {
	}

	public function addTransaction($khalti_order_id, $transactionid, $type, $total, $currency) {
		
	}

	public function getTotalCaptured($khalti_order_id) {
	}

	public function getTotalRefunded($khalti_order_id) {
	}

}