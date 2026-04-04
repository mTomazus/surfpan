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
	    $this->module('trongate_administrators');
	    $this->trongate_administrators->_make_sure_allowed();
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
		$this->module('trongate_security');
		$this->module('competitions');
		$this->trongate_security->_make_sure_allowed('judges area');

		$judge = $this->competitions->_get_judge_info();

		$this->validation->set_rules('message', 'Message', 'required');
		if ($this->validation->run() === true) {
			$data['message'] = post('message', true);
			$data['name'] = $judge->name ?? 'N/A';
			$data['email'] = $judge->email;
			$data['phone'] = $judge->phone;
			$data['organization'] = $judge->organization ?? 'N/A';

			$message = $this->view('contact_email_template', $data, true);
			mail('info@surfpan.com', 'Contact Form Submission', $message);
			http_response_code(200);
			set_flashdata('Your message has been sent successfully!');
			redirect('welcome/contacts');

		} else {

			validation_errors(400);

		}
	}

	function test(){
		$data['view_file'] = 'test';
		$this->template('public', $data);
	}
}