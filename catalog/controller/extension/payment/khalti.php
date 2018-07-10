<?php
class ControllerExtensionPaymentKhalti extends Controller {
	public function index() {
		$this->load->model('checkout/order');

		$this->load->language('extension/payment/khalti');

		if($this->config->get('payment_khalti_test') == 0){
			//live mode
			$this->khalti_public_key = $this->config->get('payment_khalti_live_public_key');
			$this->khalti_private_key = $this->config->get('payment_khalti_live_secret_key');;
		} else {
			$this->khalti_public_key = $this->config->get('payment_khalti_test_public_key');;
			$this->khalti_private_key = $this->config->get('payment_khalti_test_secret_key');;
		}

		$data['khalti_public_key'] = $this->khalti_public_key;


		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['transaction_id'] = $this->session->data['order_id'];
		$data['return_url'] = $this->url->link('extension/payment/khalti/callback');
		$data['language'] = $this->session->data['language'];
		$data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['pay_from_email'] = $order_info['email'];
		$data['firstname'] = $order_info['payment_firstname'];
		$data['lastname'] = $order_info['payment_lastname'];
		$data['address'] = $order_info['payment_address_1'];
		$data['address2'] = $order_info['payment_address_2'];
		$data['phone_number'] = $order_info['telephone'];
		$data['postal_code'] = $order_info['payment_postcode'];
		$data['city'] = $order_info['payment_city'];
		$data['state'] = $order_info['payment_zone'];
		$data['country'] = $order_info['payment_iso_code_3'];
		$data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false)*100;
		$data['currency'] = $order_info['currency_code'];

		$products = '';

		foreach ($this->cart->getProducts() as $product) {
			$products .= $product['quantity'] . ' x ' . $product['name'] . ', ';
		}

		$data['detail1_text'] = $products;

		$data['order_id'] = $this->session->data['order_id'];

		$data['site_url'] = $this->config->get('config_url');

		//only show the order button if the currenc is NPR
		if($order_info['currency_code'] == "NPR"){
			return $this->load->view('extension/payment/khalti', $data);
		}
	}

	public function callback() {
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);
		// var_dump($order_info);	die();
		if ($order_info) {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));

			$verified = true;

			$token = $this->request->get['token'];
			$amount = $this->request->get['amount'];
			$khalti_order_id = $this->request->get['order_id'];
			$validate = $this->khalti_validate($token,$amount);
			$status_code = $validate['status_code'];
			$idx = $validate['idx'];
			$amount = $amount/100;
			if((int)$order_info['total'] == $amount && $idx!= null && $status_code == 200 && $khalti_order_id == $order_info['order_id']){
				$this->model_checkout_order->addOrderHistory($order_id, 5, '', true);
				$this->response->redirect($this->url->link('checkout/success'));
			} else {
				$this->model_checkout_order->addOrderHistory($order_id, 10, '', true);
				$this->response->redirect($this->url->link('checkout/failure'));
			}
		}
	}

	private function khalti_validate($token,$amount)
    {
        $args = http_build_query(array(
            'token' => $token,
            'amount'  => $amount
           ));

        $url = "https://khalti.com/api/payment/verify/";

        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$args);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if($this->config->get('payment_khalti_test') == 0){
			//live mode
			$this->khalti_public_key = $this->config->get('payment_khalti_live_public_key');
			$this->khalti_private_key = $this->config->get('payment_khalti_live_secret_key');;
		} else {
			$this->khalti_public_key = $this->config->get('payment_khalti_test_public_key');;
			$this->khalti_private_key = $this->config->get('payment_khalti_test_secret_key');;
		}

        $headers = ['Authorization: Key '.$this->khalti_private_key];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Response
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($response);
        $idx = @$response->idx;
        $data = array(
            "idx" => $idx,
            "status_code" => $status_code,
            "response" => $response
        );
        curl_close($ch);
        return $data;
    }

    private function khalti_transaction($idx)
    {
        $url = "https://khalti.com/api/merchant-transaction/$idx/";

        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		if($this->config->get('payment_khalti_test') == 0){
			//live mode
			$this->khalti_public_key = $this->config->get('payment_khalti_live_public_key');
			$this->khalti_private_key = $this->config->get('payment_khalti_live_secret_key');;
		} else {
			$this->khalti_public_key = $this->config->get('payment_khalti_test_public_key');;
			$this->khalti_private_key = $this->config->get('payment_khalti_test_secret_key');;
		}

		$headers = ['Authorization: Key '.$this->khalti_private_key];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Response
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }
}