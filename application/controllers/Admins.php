<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admins extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
    }

    //check session
    private function _is_logged_in() {
        if($this->session->userdata('admin_id')){
            return true;        
        } else {
            return false;
        }
    }

    //touched
	public function index()
	{
		if($this->session->userdata('admin_id')) {
			$data['admin_info'] = $this->Admin->get_admin($this->session->userdata('admin_id'));
			$data['papers'] = $this->Admin->get_all_papers();
			$this->load->view('admins/dashboard',$data);
		}
		else{
			$data['message'] = "";
			$this->load->view('admins/login',$data);
		}
	}

	//touched
	public function papers(){
		if($this->_is_logged_in()){
			$data['papers'] = $this->Admin->get_all_papers();
			$data['query']  = $this->Admin->get_last_query();
			$this->load->view('admins/papers',$data);
		}
		else{
			redirect('admins/index');
		}
	}

	// //touched
	// public function signup()
	// {
	// 	$this->load->view('admins/signup');
	// }

	//touched
	public function login()
	{
		//if already logged in
		if($this->session->userdata('admin_id')) {
			$data['admin_info'] = $this->Admin->get_admin($this->session->userdata('admin_id'));
			redirect('admins/index');
		}
		else{
			$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
			$this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
	        $this->form_validation->set_rules('password', 'password', 'trim|required');

	        //failed login
	        if ($this->form_validation->run() == FALSE)
	        {
	        	$data['message'] = "";
	            $this->load->view('admins/login',$data);
	        }
	        else
	        {
	            $email = $this->input->post('email');
	            $password = $this->input->post('password');

	            if(!$this->Admin->login_admin($email, $password))
				{
					$data['message'] = "Invalid email or password";
					$this->load->view('admins/login', $data);
				}
				//successful login
				else
				{
					if($this->session->userdata('admin_id')) {
						$data['admin_info'] = $this->Admin->get_admin($this->session->userdata('admin_id'));
					}
					//$this->load->view('admins/dashboard', $data);
					redirect('admins/index');
				}
	        }
    	}
	}

	// public function validate()
	// {
	// 	$this->form_validation->set_error_delimiters('<div class="error">', '</div>');
	// 	$this->form_validation->set_rules('first_name', 'First Name', 'required');
 //  	    $this->form_validation->set_rules('last_name', 'Last Name', 'required');
 //  	    $this->form_validation->set_rules('phone_number', 'Phone Number', 'required');
 //        $this->form_validation->set_rules('password', 'Password', 'required|min_length[2]|alpha_numeric');
 //        $this->form_validation->set_rules('passconf', 'Confirm Password', 'required|matches[password]|min_length[2]|alpha_numeric');
 //        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

 //        if ($this->form_validation->run() == FALSE)
 //        {
 //            $this->load->view('admins/signup');
 //        }
 //        else
 //        {
 //        	if($this->Admin->exists($this->input->post('email')))
 //        	{
 //        		$data['message'] = "email is already in use";
 //        		$this->load->view('admins/signup', $data);
 //        	}
 //        	else{
 //        		$first_name = $this->input->post('first_name');
 //        		$last_name = $this->input->post('last_name');
 //        		$phone = $this->input->post('phone_number');
 //        		$dob = $this->input->post('dob');
 //        		$email = $this->input->post('email');
 //                $password = $this->input->post('password');
 //                //check if inserted into db
 //                if($this->Admin->signup_new_user($first_name, $last_name, $phone, $dob, $email, $password)){
 //                	$data['message'] = "Your account is created";
 //        			$this->load->view('admins/login', $data);
 //                }
 //        	}
            
 //        }
	// }

	public function editinfo(){
		if($this->_is_logged_in()){
			$data['admin_info'] = $this->Admin->get_admin($this->session->userdata('admin_id'));
			$this->load->view('admins/editinfo',$data);
		}
		else{
			redirect('admins/index');
		}
	}

	public function saveinfo(){
		if($this->_is_logged_in()){
			$admin_data = array(
				'address_line_1' => $this->input->post('address_line_1'),
				'address_line_2' => $this->input->post('address_line_2'),
				'city' => $this->input->post('city'),
				'country' => $this->input->post('country'),
				'description' => $this->input->post('description'),
				'affiliation' => $this->input->post('affiliation'),
				'website' => $this->input->post('website'),
			);

			if($this->Admin->saveinfo($this->session->userdata('admin_id'), $admin_data)){
				redirect('admins/index');
			}
			else{
				redirect('admins/editinfo');
			}
		}
		else{
			redirect('admins/index');
		}
	}

	public function view($paper_id){
		if($this->_is_logged_in()){
			if($this->PartialModel->is_valid_paper($paper_id)){
				$data['paper_data'] = $this->Admin->get_paper_by_id($paper_id);
				$data['co_author_data'] = $this->Admin->get_co_author_by_id($paper_id);
				//print_r($data['co_author_data']);
				$data['reviewers'] = $this->Admin->get_all_reviewers();
				$data['assigned_reviewers'] = $this->Admin->get_assigned_reviewers($paper_id);
				//print_r($data['assigned_reviewers'] );
				//echo $this->Admin->get_last_query();
				$this->load->view('admins/paperform',$data);
			}
			else{
				redirect('admins/papers');
			}
		}
		else{
			redirect('admins/index');
		}
	}

	public function assign_paper(){
		if($this->_is_logged_in()){
			$reviewers = $this->input->post('reviewers');
			$paper_id = $this->input->post('paper_id');

			if($this->Admin->assign_paper($paper_id, $reviewers)){
				echo json_encode(array("result" => TRUE));
			}
			else{
				echo json_encode(array("result" => FALSE));
			}
		}
		else{
			redirect('admins/index');
		}
	}

	//touched
	public function logout(){
		$this->session->unset_userdata('admin_id');
		redirect('admins/login');
	}

	public function paper_delete($paper_id){
		if($this->_is_logged_in()){
		    $query = $this->Admin->delete_by_id($paper_id);
		    if($query){
				echo json_encode(array("result" => TRUE));
			}
			else{
				echo json_encode(array("result" => FALSE));
			}
		}
		else{
			redirect('admins/index');
		}
	}

	public function showpaper($paper_name){
		if($this->_is_logged_in()){
			$data['paper_name'] = $paper_name;
			$this->load->view('admins/showpaper',$data);
		}
		else{
			redirect('admins/index');
		}
	}

	public function show($paper_id){
		if($this->_is_logged_in()){
			if($this->PartialModel->is_valid_paper($paper_id)){
				$data['paper_data'] = $this->Admin->get_paper_by_id($paper_id);
				$data['co_author_data'] = $this->Admin->get_co_author_by_id($paper_id);
				$data['paper_files_data'] = $this->Author->get_files_by_id($paper_id);
				$data['assigned_reviewers'] = $this->Admin->get_assigned_reviewers_details($paper_id);
				$data['review_data'] = $this->Admin->get_review_history($paper_id);

				foreach ($data['review_data'] as $rev) {
					//appending score text for showing comment timeline
					$rev->review_score_text = $this->PartialModel->return_score_text($rev->review_score);
				}

				$this->load->view('admins/show',$data);
			}
			else{
				redirect('admins/papers');
			}
		}
		else{
			redirect('admins/index');
		}
	}

	public function settings(){
		if($this->_is_logged_in()){
			date_default_timezone_set('Asia/Dhaka');
	        $unique_id = $this->session->userdata('admin_id').time().$this->session->userdata('admin_id');
	        $data['invitation_id'] = $unique_id;
			$this->load->view('admins/settings',$data);
		}
		else{
			redirect('admins/index');
		}
	}

	public function send_invitation(){
		if($this->_is_logged_in()){
			$invitation_id = $this->input->post('invitation_id');
			$email = $this->input->post('email');
			$reg_link = base_url('reviewers/register/').$invitation_id;

			// Load PHPMailer library
	        $this->load->library('phpmailer_lib');
	        // PHPMailer object
	        $mail = $this->phpmailer_lib->load();
	        // Add a recipient
	        $mail->addAddress($email);
	        // Email subject
	        $mail->Subject = 'Invitation on ConfMag';  
	        // Email body content
	        $mailContent = "<h1>Invitation on ConfMag</h1>
	            <p>Register as a reviewer on ConfMag</p>
	            <a href=$reg_link target='_blank'>Register as a reviewer on ConfMag</a>";
	        $mail->Body = $mailContent;
	        // Send email
	        if(!$mail->send()){
	            echo 'Message could not be sent.';
	            echo 'Mailer Error: ' . $mail->ErrorInfo;
	        }else{
	            echo 'Message has been sent';
	        }


			$this->Admin->send_invitation($invitation_id, $email);
		}
		else{
			redirect('admins/index');
		}
	}

	public function download_all_papers(){
		if($this->_is_logged_in()){
		    $this->load->library('zip');

		    $path = FCPATH.'/uploads/';
		    //$path = base_url().'/uploads/';

		    $this->zip->read_dir($path);

		    // Download the file to your desktop. Name it "my_backup.zip"
		    $this->zip->download('confmag_papers.zip');
		}
		else{
			redirect('admins/index');
		}
	}
}
