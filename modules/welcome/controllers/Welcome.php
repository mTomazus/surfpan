<?php
class Welcome extends Trongate {

	/**
	 * Display the default welcome page.
	 *
	 * @return void
	 */
	public function index(): void {
		$data['view_module'] = 'welcome';
		$data['view_file'] = 'welcome';
		$this->template('public', $data);
	}

	/**
	 * Display the optional database setup instructions page.
	 *
	 * @return void
	 */
	public function database_setup(): void {
	    $this->view('database_setup');
	}

	public function math(){
	    $this->view('math');
	}

	function privacy(){
		if (from_trongate_mx()) {
			$this->view('privacy_policy');
			return;
		}
		$data['view_file'] = 'privacy_policy';
		$this->template('public', $data);
	}

	function terms(){
		if (from_trongate_mx()) {
			$this->view('terms_conditions');
			return;
		}
		$data['view_file'] = 'terms_conditions';
		$this->template('public', $data);
	}

	function contacts(){
		if (from_trongate_mx()) {
			$this->view('contacts_modal');
			return;
		}
		$data['view_file'] = 'contacts_modal';
		$this->template('public', $data);
	}

	function contact_form(){
		if (from_trongate_mx()) {
			$this->view('contacts_form');
			return;
		}
		$data['view_file'] = 'contacts_form';
		$this->template('public', $data);
	}

	function submit_form(){
		$this->validation->set_rules('name', 'Name', 'required');
		$this->validation->set_rules('email', 'Email', 'required|valid_email');
		$this->validation->set_rules('message', 'Message', 'required');
		if ($this->validation->run() == false) {
			echo json_encode(['status' => 'error', 'message' => validation_errors()]);
			return;
		}
		$name = post('name', true);
		$email = post('email', true);
		$message = post('message', true);
		// Here you would typically send the email using a mail library or service
		// For demonstration, we'll just return a success message
		echo json_encode(['status' => 'success', 'message' => 'Thank you for your message, ' . $name . '! We will get back to you shortly.']);
	}

	function test(){
		$data['view_file'] = 'test';
		$this->template('public', $data);
	}
}