<?php

defined('BASEPATH') or exit('No direct script access allowed');
#[\AllowDynamicProperties]
class MailCtl extends CI_Controller
{
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function projectOpportunity()
	{
		$this->load->library('email');
		$this->load->helper('date');
		$time = now();
		$data = [
			'name' => $this->input->post('name'),
			'email' => $this->input->post('email'),
			'phoneNumber' => $this->input->post('phoneNumber'),
			'company' => $this->input->post('company'),
			'projectDescription' => $this->input->post('projectDescription'),
			'category' => $this->input->post('category'),
			'budget' => $this->input->post('budget'),
			'recipientEmail' => $this->input->post('email'),
			'randomness' => $time
		];

		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'jpg|jpeg|png|pdf|doc|docx|svg|ppt'; // SVG, PNG, JPG, DOCS, PDF or PPT
		$config['encrypt_name'] = true;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('projectBrief')) {
			$error = $this->upload->display_errors();
			$projectBriefPath = null;
			$projectBriefName = null;
		} else {
			$uploadData = $this->upload->data();
			$projectBriefPath = $uploadData['full_path'];
			$projectBriefName = $uploadData['file_name'];
		}

		$this->load->config('email');
		$toProjectOppertunity = $this->config->item('projectOppertunity');

		// Set up email
		$this->email->from('mailservicehexavara@gmail.com', 'Hexavara Mail');
		$this->email->to($toProjectOppertunity);
		$this->email->subject('New Project Opportunity');
		$this->email->message($this->load->view('template1', $data, true));
		$this->email->set_mailtype('html');

		// Attach file if uploaded
		if ($projectBriefPath) {
			$this->email->attach($projectBriefPath, 'attachment', $projectBriefName);
		}

		// Send email and return response
		if ($this->email->send()) {
			if ($projectBriefPath && file_exists($projectBriefPath)) {
				unlink($projectBriefPath);
			}
			$response = [
				'status' => 'success',
				'message' => 'Form submitted successfully',
				'data' => $projectBriefPath
			];
		} else {
			$response = [
				'status' => 'error',
				'message' => 'Failed to send email.'
			];
		}

		echo json_encode($response);
	}
}
