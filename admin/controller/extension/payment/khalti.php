<?php
class ControllerExtensionPaymentKhalti extends Controller {

    private $error = array();

	public function index() {

		$this->load->language('extension/payment/khalti');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$page =  @$this->request->get['page'];

		$data['current'] = $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('payment_khalti', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['live_public_key'])) {
			$data['error_live_public_key'] = $this->error['live_public_key'];
		} else {
			$data['error_live_public_key'] = '';
		}

		if (isset($this->error['live_secret_key'])) {
			$data['error_live_secret_key'] = $this->error['live_secret_key'];
		} else {
			$data['error_live_secret_key'] = '';
		}

		if (isset($this->error['test_public_key'])) {
			$data['error_test_public_key'] = $this->error['test_public_key'];
		} else {
			$data['error_test_public_key'] = '';
		}

		if (isset($this->error['test_secret_key'])) {
			$data['error_test_secret_key'] = $this->error['test_secret_key'];
		} else {
			$data['error_test_secret_key'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/khalti', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/khalti', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->request->post['payment_khalti_test'])) {
			$data['payment_khalti_test'] = $this->request->post['payment_khalti_test'];
		} else {
			$data['payment_khalti_test'] = $this->config->get('payment_khalti_test');
		}


		if (isset($this->request->post['payment_khalti_live_secret_key'])) {
			$data['payment_khalti_live_secret_key'] = $this->request->post['payment_khalti_live_secret_key'];
		} else {
			$data['payment_khalti_live_secret_key'] = $this->config->get('payment_khalti_live_secret_key');
		}

		if (isset($this->request->post['payment_khalti_live_public_key'])) {
			$data['payment_khalti_live_public_key'] = $this->request->post['payment_khalti_live_public_key'];
		} else {
			$data['payment_khalti_live_public_key'] = $this->config->get('payment_khalti_live_public_key');
		}

		if (isset($this->request->post['payment_khalti_test_secret_key'])) {
			$data['payment_khalti_test_secret_key'] = $this->request->post['payment_khalti_test_secret_key'];
		} else {
			$data['payment_khalti_test_secret_key'] = $this->config->get('payment_khalti_test_secret_key');
		}

		if (isset($this->request->post['payment_khalti_test_public_key'])) {
			$data['payment_khalti_test_public_key'] = $this->request->post['payment_khalti_test_public_key'];
		} else {
			$data['payment_khalti_test_public_key'] = $this->config->get('payment_khalti_test_public_key');
		}

		if (isset($this->request->post['payment_khalti_status'])) {
			$data['payment_khalti_status'] = $this->request->post['payment_khalti_status'];
		} else {
			$data['payment_khalti_status'] = $this->config->get('payment_khalti_status');
		}

		if($this->config->get('payment_khalti_test') == 0){
			//live mode
			$this->khalti_public_key = $this->config->get('payment_khalti_live_public_key');
			$this->khalti_private_key = $this->config->get('payment_khalti_live_secret_key');;
		} else {
			$this->khalti_public_key = $this->config->get('payment_khalti_test_public_key');;
			$this->khalti_private_key = $this->config->get('payment_khalti_test_secret_key');;
		}

	$transaction_id = @$_GET['transaction_id'];
    $transaction = array();
	$getTransaction = $this->getTransaction()['Response'];
	$getTransaction = json_decode($getTransaction);
    foreach($getTransaction->records as $t)
    {
        array_push($transaction, array(
            'idx' 		=> $t->idx,
            'source' 	=> $t->user->name,
            'amount' 	=> $t->amount/100,
            'fee' 		=> $t->fee_amount/100,
            'date' 		=> date("Y/m/d H:m:s", strtotime($t->created_on)),
            'type' 		=> $t->type->name,
            'state' 	=> $t->refunded == true ? "Refunded" : $t->state->name,
			'refunded' 	=> $t->refunded,
			'link' 		=> $data['current'].'&transaction_id='.$t->idx
        ));
	}
	
	$data['header'] = $this->load->controller('common/header');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['footer'] = $this->load->controller('common/footer');

	$data['transactions'] = $transaction;
    if($transaction_id)
    {
      if(@$_GET['refund'] == 'true')
      {
        $refund = $this->khaltiRefund($transaction_id);
        $status_code = $refund['StatusCode'];
        $detail = json_decode($refund['Response']);
        $detail = $detail->detail;
        if($status_code == 200)
        {
			$data['refund_status'] = "success";
			$data['refund_message'] = $detail;
        }
        else
        {
			$data['refund_status'] = "failed";
			$data['refund_message'] = $detail;
        }
	  }

	  $transaction_detail = json_decode($this->getTransactionDetail($transaction_id)['Response']);
                //
                $transaction_detail_array = array(
                    "idx" => $transaction_detail->idx,
                    "source" => $transaction_detail->user->name,
                    "mobile" => $transaction_detail->user->mobile,
                    "amount" => $transaction_detail->amount/100,
                    "fee_amount" => $transaction_detail->fee_amount/100,
                    "date" => date("Y/m/d H:m:s", strtotime($transaction_detail->created_on)),
                    "state" => $transaction_detail->refunded == true ? "Refunded" : $transaction_detail->state->name,
					"refunded" => $transaction_detail->refunded,
					"refund_link"  => $this->url->link('extension/payment/khalti','user_token='.$this->session->data['user_token'].'&transaction_id='.$transaction_detail->idx.'&refund=true'),
					"back_link" => $this->url->link('extension/payment/khalti','user_token='.$this->session->data['user_token'])
                );
	  $data['transaction_detail'] = $transaction_detail_array;
	  $this->response->setOutput($this->load->view('extension/payment/khalti_transaction', $data));
    }
    else
    {
		$this->response->setOutput($this->load->view('extension/payment/khalti', $data));
	}
	}

	private function getTransaction()
    {
        $url = "https://khalti.com/api/merchant-transaction/";

        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = ['Authorization: Key '.$this->khalti_private_key];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Response
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array(
            "Response" => $response,
            "StatusCode" => $status_code
        );
    }

    private function getTransactionDetail($idx)
    {
        $url = "https://khalti.com/api/merchant-transaction/{$idx}/";

        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $headers = ['Authorization: Key '.$this->khalti_private_key];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Response
        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
            return array(
                "Response" => $response,
                "StatusCode" => $status_code
            );
    }

    private function khaltiRefund($idx)
    {
        $url = "https://khalti.com/api/merchant-transaction/{$idx}/refund/";
        # Make the call using API.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

        $headers = ['Authorization: Key '.$this->khalti_private_key];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Response
		$response = curl_exec($ch);

        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
            return array(
                "Response" => $response,
                "StatusCode" => $status_code
            );
    }
}
