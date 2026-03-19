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

	function test(){
		$data['view_file'] = 'test';
		$this->template('public', $data);
	}
}