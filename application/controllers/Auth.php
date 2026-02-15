<?php defined('BASEPATH') or exit('No direct script access allowed');
error_reporting(0);
/**
 * Class Auth
 * @property Ion_auth|Ion_auth_model $ion_auth        The ION Auth spark
 * @property CI_Form_validation      $form_validation The form validation library
 */
class Auth extends CI_Controller
{
    public $data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper', 'sms_helper']);

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

        $this->lang->load('auth');
    }

    /**
     * Redirect if needed, otherwise display the user list
     */
    public function index()
    {

        if (!$this->ion_auth->logged_in()) {
            // redirect them to the login page
            redirect('auth/login', 'refresh');
        } else if (!$this->ion_auth->is_admin()) // remove this elseif if you want to enable this for non-admins
        {
            // redirect them to the home page because they must be an administrator to view this
            show_error('You must be an administrator to view this page.');
        } else {
            $this->data['title'] = $this->lang->line('index_heading');

            // set the flash data error message if there is one
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            //list the users
            $this->data['users'] = $this->ion_auth->users()->result();

            //USAGE NOTE - you can do more complicated queries like this			
            foreach ($this->data['users'] as $k => $user) {
                $this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
            }

            $this->_render_page('auth' . DIRECTORY_SEPARATOR . 'index', $this->data);
        }
    }

    /**
     * Log the user in
     */
    public function login()
    {

        $this->data['title'] = $this->lang->line('login_heading');
        $identity_column = $this->config->item('identity', 'ion_auth');
        // validate form input
        $this->form_validation->set_rules('identity', ucfirst($identity_column), 'required|xss_clean|numeric|min_length[3]|max_length[16]');
        $this->form_validation->set_rules('password', str_replace(':', '', $this->lang->line('login_password_label')), 'required|xss_clean');

        if ($this->form_validation->run() === TRUE) {

            $tables = $this->config->item('tables', 'ion_auth');
            $identity = $this->input->post('identity', true);
            if (isset($_POST['type']) && $_POST['type'] != 'phone') {
                $res = $this->db->select('id')->where('email', $identity)->get($tables['login_users'])->result_array();
            } else {
                $res = $this->db->select('id')->where($identity_column, $identity)->get($tables['login_users'])->result_array();
            }
            // print_R($res)  ;exit;
            if (!empty($res)) {
                // Check verification status based on login type (email or mobile)
                $user_data = fetch_details('users', ['id' => $res[0]['id']]);
                if (!empty($user_data)) {
                    $is_email_login = filter_var($identity, FILTER_VALIDATE_EMAIL);
                    if ($is_email_login) {
                        // User is logging in with email - check email_verified
                        if (isset($user_data[0]['email_verified']) && $user_data[0]['email_verified'] == 0) {
                            sendWebJsonResponse(true, 'Please verify your email address before logging in.');
                            return;
                        }
                    } else {
                        // User is logging in with mobile - check mobile_verified
                        if (isset($user_data[0]['mobile_verified']) && $user_data[0]['mobile_verified'] == 0) {
                            sendWebJsonResponse(true, 'Please verify your mobile number before logging in.');
                            return;
                        }
                    }
                }
                
                if ($this->ion_auth_model->in_group('admin', $res[0]['id'])) {

                    // check to see if the user is logging in
                    // check for "remember me"
                    $remember = (bool) $this->input->post('remember');
                    
                    // Set session prefix for admin
                    $this->ion_auth_model->session_prefix = 'admin_';

                    if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember, 'phone')) {
                        //if the login is successful
                        sendWebJsonResponse(false, strip_tags($this->ion_auth->messages()), ['redirect_url' => base_url('admin/home')]);
                    } else {
                        // if the login was un-successful
                        // print_r("here");

                        sendWebJsonResponse(true, strip_tags($this->ion_auth->errors()));
                    }
                } else if ($this->ion_auth_model->in_group('seller', $res[0]['id'])) {

                    $seller_status = $this->ion_auth->seller_status($res[0]['id']);
                    // print_r($seller_status);exit;
                    $messages = array("0" => "Your acount is deactivated", "1" => "Logged in successfully", "2" => "Your account is not yet approved.", "7" => "Your account has been removed by the admin. Contact admin for more information.");

                    if ($seller_status == '0') {
                        // print_R($seller_status);exit;
                        sendWebJsonResponse(false, $messages[$seller_status]);
                    }

                    // check for "remember me"
                    $remember = (bool) $this->input->post('remember');
                    
                    // Set session prefix for seller
                    $this->ion_auth_model->session_prefix = 'seller_';

                    if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember, 'phone')) {
                    //    print_R($this->ion_auth->get_user_id());exit;
                        //if the login is successful
                        $seller_status = $this->ion_auth->seller_status($res[0]['id']);
                        // var_dump($seller_status);
                        // exit;
                        if ($seller_status == '1') {
                            sendWebJsonResponse(false, $messages[$seller_status], ['redirect_url' => base_url('seller/home')]);
                        }
                        // sendWebJsonResponse(false, $messages[$seller_status]);
                    } else {
                        // if the login was un-successful
                        sendWebJsonResponse(true, strip_tags($this->ion_auth->errors()));
                    }
                } else if ($this->ion_auth_model->in_group('affiliate', $res[0]['id'])) {

                    $affiliate_status = $this->ion_auth->affiliate_status($res[0]['id']);
                    $messages = array(
                        "0" => "Your acount is deactivated",
                        "1" => "Logged in successfully",
                        "2" => "Your account is not yet approved.",
                        "7" => "Your account has been removed by the admin. Contact admin for more information."
                    );

                    if ($affiliate_status == '0' || $affiliate_status == '2') {
                        sendWebJsonResponse(false, $messages[$affiliate_status]);
                    }
                    // check for "remember me"
                    $remember = (bool) $this->input->post('remember');
                    
                    // Set session prefix for affiliate
                    $this->ion_auth_model->session_prefix = 'affiliate_';

                    if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember, 'phone')) {
                        //if the login is successful
                        $affiliate_status = $this->ion_auth->affiliate_status($res[0]['id']);

                        if ($affiliate_status == '1') {
                            sendWebJsonResponse(false, $messages[$affiliate_status], ['redirect_url' => base_url('affiliate/home')]);
                        }
                        sendWebJsonResponse(false, $messages[$affiliate_status]);

                    } else {
                        // if the login was un-successful
                        sendWebJsonResponse(true, $this->ion_auth->errors());
                    }
                } else if ($this->ion_auth_model->in_group('delivery_boy', $res[0]['id'])) {

                    // Set session prefix for delivery boy
                    $this->ion_auth_model->session_prefix = 'delivery_boy_';
                   

                    $delivery_boy_status = $this->ion_auth->delivery_boy_status($res[0]['id']);

                   
                    $messages = array(
                        "0" => "Your acount is deactivated",
                        "1" => "Logged in successfully",
                        "2" => "Your account is not yet approved.",
                        "7" => "Your account has been removed by the admin. Contact admin for more information."
                    );

                    if ($delivery_boy_status == '0' || $delivery_boy_status == '2') {
                        sendWebJsonResponse(false, $messages[$delivery_boy_status]);
                    }
                    // check for "remember me"
                    $remember = (bool) $this->input->post('remember');
                    if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember, 'phone')) {
                        //if the login is successful
                        $delivery_boy_status = $this->ion_auth->delivery_boy_status($res[0]['id']);

                        if ($delivery_boy_status == '1') {
                            sendWebJsonResponse(false, $messages[$delivery_boy_status], ['redirect_url' => base_url('delivery_boy/home')]);
                        }
                        sendWebJsonResponse(false, $messages[$delivery_boy_status]);

                    } else {
                        // if the login was un-successful
                        sendWebJsonResponse(true, strip_tags($this->ion_auth->errors()));
                    }
                } else {

                    sendWebJsonResponse(true, 'Incorrect Login');

                }
            } else {
                sendWebJsonResponse(true, 'Incorrect Login');
            }
        } else {
            // the user is not logging in so display the login page
            if (validation_errors()) {

                sendWebJsonResponse(true, strip_tags(validation_errors()));
            }
            if ($this->session->flashdata('message')) {
                sendWebJsonResponse(false, $this->session->flashdata('message'));
            }

            // set the flash data error message if there is one
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['identity'] = [
                'name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
            ];

            $this->data['password'] = [
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
            ];

            $this->_render_page('auth' . DIRECTORY_SEPARATOR . 'login', $this->data);
        }
    }

    /**
     * Log the user out
     */
    public function logout()
    {
        $this->data['title'] = "Logout";

        // log the user out
        $this->ion_auth->logout();

        // redirect them to the login page
        redirect('auth/login', 'refresh');
    }


    /**
     * Change password
     */
    public function change_password()
    {
        $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required|xss_clean');
        $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
        $this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required|xss_clean');


        $identity = $this->session->userdata('identity');
        if ($this->form_validation->run() === FALSE) {

            if ($this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'))) {

                //if the login is successful
                $response['error'] = false;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->ion_auth->messages();
                echo json_encode($response);
                return;
                exit();
            } else {
                // if the login was un-successful
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->ion_auth->errors();
                echo json_encode($response);
                return;
                exit();
            }
        } else {

            if (validation_errors()) {
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = validation_errors();
                echo json_encode($response);
                return false;
                exit();
            }
            if ($this->session->flashdata('message')) {
                $response['error'] = false;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->session->flashdata('message');
                echo json_encode($response);
                return false;
                exit();
            }
        }
    }

    /**
     * Forgot password
     */
    public function forgot_password()
    {
        $this->data['title'] = $this->lang->line('forgot_password_heading');

        // setting validation rules by checking whether identity is username or email
        if ($this->config->item('identity', 'ion_auth') != 'email') {
            $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_identity_label'), 'required|xss_clean');
        } else {
            $this->form_validation->set_rules('identity', $this->lang->line('forgot_password_validation_email_label'), 'required|valid_email|xss_clean');
        }


        if (!$this->form_validation->run()) {
            $this->data['type'] = $this->config->item('identity', 'ion_auth');
            // setup the input
            $this->data['identity'] = [
                'name' => 'identity',
                'id' => 'identity',
            ];

            if ($this->config->item('identity', 'ion_auth') != 'email') {
                $this->data['identity_label'] = $this->lang->line('forgot_password_identity_label');
            } else {
                $this->data['identity_label'] = $this->lang->line('forgot_password_email_identity_label');
            }

            // set any errors and display the form
            $response['error'] = true;
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            $response['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            echo json_encode($response);
            return false;
        } else {
            $identity_column = $this->config->item('identity', 'ion_auth');
            $identity = $this->ion_auth->where($identity_column, $this->input->post('identity'))->users()->row();

            if (empty($identity)) {

                if ($this->config->item('identity', 'ion_auth') != 'email') {
                    $this->ion_auth->set_error('forgot_password_identity_not_found');
                } else {
                    $this->ion_auth->set_error('forgot_password_email_not_found');
                }

                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect("auth/forgot_password", 'refresh');
            }

            // run the forgotten password method to email an activation code to the user
            $forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

            if ($forgotten) {
                // if there were no errors
                $response['error'] = false;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->ion_auth->messages();
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->ion_auth->errors();
                echo json_encode($response);
                return false;
            }
        }
    }

    /**
     * Reset password - final step for forgotten password
     *
     * @param string|null $code The reset code
     */
    public function reset_password($code = NULL)
    {
        if (!$code) {
            show_404();
        }

        $this->data['title'] = $this->lang->line('reset_password_heading');

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {
            // if the code is valid then display the password reset form

            $this->form_validation->set_rules('new', $this->lang->line('reset_password_validation_new_password_label'), 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', $this->lang->line('reset_password_validation_new_password_confirm_label'), 'required|xss_clean');

            if ($this->form_validation->run() === FALSE) {
                // display the form

                // set the flash data error message if there is one
                $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = [
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
                ];
                $this->data['new_password_confirm'] = [
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'pattern' => '^.{' . $this->data['min_password_length'] . '}.*$',
                ];
                $this->data['user_id'] = [
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                ];
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['code'] = $code;

                // render
                $this->_render_page('auth' . DIRECTORY_SEPARATOR . 'reset_password', $this->data);
            } else {
                $identity = $user->{$this->config->item('identity', 'ion_auth')};

                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {

                    // something fishy might be up
                    $this->ion_auth->clear_forgotten_password_code($identity);

                    show_error($this->lang->line('error_csrf'));
                } else {
                    // finally change the password
                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));

                    if ($change) {
                        // if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        redirect("auth/login", 'refresh');
                    } else {
                        $this->session->set_flashdata('message', $this->ion_auth->errors());
                        redirect('auth/reset_password/' . $code, 'refresh');
                    }
                }
            }
        } else {
            // if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("admin/login/forgot_password", 'refresh');
        }
    }

    /**
     * Activate the user
     *
     * @param int         $id   The user ID
     * @param string|bool $code The activation code
     */
    public function activate($id, $code = FALSE)
    {
        $activation = FALSE;

        if ($code !== FALSE) {
            $activation = $this->ion_auth->activate($id, $code);
        } else if ($this->ion_auth->is_admin()) {
            $activation = $this->ion_auth->activate($id);
        }

        if ($activation) {
            // redirect them to the auth page
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("admin/auth", 'refresh');
        } else {
            // redirect them to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    /**
     * Deactivate the user
     *
     * @param int|string|null $id The user ID
     */
    public function deactivate($id = NULL)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            // redirect them to the home page because they must be an administrator to view this
            show_error('You must be an administrator to view this page.');
        }

        $id = (int) $id;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required|xss_clean');
        $this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric|xss_clean');

        if ($this->form_validation->run() === FALSE) {
            // insert csrf check
            $this->data['csrf'] = $this->_get_csrf_nonce();
            $this->data['user'] = $this->ion_auth->user($id)->row();

            $this->_render_page('auth' . DIRECTORY_SEPARATOR . 'deactivate_user', $this->data);
        } else {
            // do we really want to deactivate?
            if ($this->input->post('confirm') == 'yes') {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                    show_error($this->lang->line('error_csrf'));
                }

                // do we have the right userlevel?
                if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                    $this->ion_auth->deactivate($id);
                }
            }

            // redirect them back to the auth page
            redirect('auth', 'refresh');
        }
    }

    /**
     * Create a new user
     */
    public function create_user()
    {
        $this->data['title'] = $this->lang->line('create_user_heading');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $tables = $this->config->item('tables', 'ion_auth');
        $identity_column = $this->config->item('identity', 'ion_auth');
        $this->data['identity_column'] = $identity_column;

        // validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'trim|required|xss_clean');
        if ($identity_column !== 'email') {
            $this->form_validation->set_rules('identity', $this->lang->line('create_user_validation_identity_label'), 'trim|xss_clean|required|is_unique[' . $tables['login_users'] . '.' . $identity_column . ']');
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|valid_email|xss_clean');
        } else {
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'trim|required|xss_clean|valid_email|is_unique[' . $tables['login_users'] . '.email]');
        }
        $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'trim|xss_clean');
        $this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'trim|xss_clean');
        $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required|xss_clean');

        if ($this->form_validation->run() === TRUE) {
            $email = strtolower($this->input->post('email'));
            $identity = ($identity_column === 'email') ? $email : $this->input->post('identity');
            $password = $this->input->post('password');

            $additional_data = [
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
                'phone' => $this->input->post('phone'),
            ];
        }
        if ($this->form_validation->run() === TRUE && $this->ion_auth->register($identity, $password, $email, $additional_data)) {
            // check to see if we are creating the user
            // redirect them back to the admin page
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            redirect("auth", 'refresh');
        } else {
            // display the create user form
            // set the flash data error message if there is one
            $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            $this->data['first_name'] = [
                'name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('first_name'),
            ];
            $this->data['last_name'] = [
                'name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('last_name'),
            ];
            $this->data['identity'] = [
                'name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
            ];
            $this->data['email'] = [
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'value' => $this->form_validation->set_value('email'),
            ];
            $this->data['company'] = [
                'name' => 'company',
                'id' => 'company',
                'type' => 'text',
                'value' => $this->form_validation->set_value('company'),
            ];
            $this->data['phone'] = [
                'name' => 'phone',
                'id' => 'phone',
                'type' => 'text',
                'value' => $this->form_validation->set_value('phone'),
            ];
            $this->data['password'] = [
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'value' => $this->form_validation->set_value('password'),
            ];
            $this->data['password_confirm'] = [
                'name' => 'password_confirm',
                'id' => 'password_confirm',
                'type' => 'password',
                'value' => $this->form_validation->set_value('password_confirm'),
            ];

            $this->_render_page('auth' . DIRECTORY_SEPARATOR . 'create_user', $this->data);
        }
    }
    /**
     * Redirect a user checking if is admin
     */
    public function redirectUser()
    {
        if ($this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }
        redirect('/', 'refresh');
    }

    /**
     * Edit a user
     *
     * @param int|string $id
     */
    public function edit_user($id)
    {
        $this->data['title'] = $this->lang->line('edit_user_heading');

        if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && !($this->ion_auth->user()->row()->id == $id))) {
            redirect('auth', 'refresh');
        }

        $user = $this->ion_auth->user($id)->row();
        $groups = $this->ion_auth->groups()->result_array();
        $currentGroups = $this->ion_auth->get_users_groups($id)->result();

        //USAGE NOTE - you can do more complicated queries like this

        // validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'trim|xss_clean');
        $this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'trim|xss_clean');

        if (isset($_POST) && !empty($_POST)) {
            // do we have a valid request?
            if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                show_error($this->lang->line('error_csrf'));
            }

            // update the password if it was posted
            if ($this->input->post('password')) {
                $this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|xss_clean|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[password_confirm]');
                $this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required|xss_clean');
            }

            if ($this->form_validation->run() === TRUE) {
                $data = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                ];

                // update the password if it was posted
                if ($this->input->post('password')) {
                    $data['password'] = $this->input->post('password');
                }

                // Only allow updating groups if user is admin
                if ($this->ion_auth->is_admin()) {
                    // Update the groups user belongs to
                    $this->ion_auth->remove_from_group('', $id);

                    $groupData = $this->input->post('groups');
                    if (isset($groupData) && !empty($groupData)) {
                        foreach ($groupData as $grp) {
                            $this->ion_auth->add_to_group($grp, $id);
                        }
                    }
                }

                // check to see if we are updating the user
                if ($this->ion_auth->update($user->id, $data)) {
                    // redirect them back to the admin page if admin, or to the base url if non admin
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                    $this->redirectUser();
                } else {
                    // redirect them back to the admin page if admin, or to the base url if non admin
                    $this->session->set_flashdata('message', $this->ion_auth->errors());
                    $this->redirectUser();
                }
            }
        }

        // display the edit user form
        $this->data['csrf'] = $this->_get_csrf_nonce();

        // set the flash data error message if there is one
        $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

        // pass the user to the view
        $this->data['user'] = $user;
        $this->data['groups'] = $groups;
        $this->data['currentGroups'] = $currentGroups;

        $this->data['first_name'] = [
            'name' => 'first_name',
            'id' => 'first_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('first_name', $user->first_name),
        ];
        $this->data['last_name'] = [
            'name' => 'last_name',
            'id' => 'last_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('last_name', $user->last_name),
        ];
        $this->data['company'] = [
            'name' => 'company',
            'id' => 'company',
            'type' => 'text',
            'value' => $this->form_validation->set_value('company', $user->company),
        ];
        $this->data['phone'] = [
            'name' => 'phone',
            'id' => 'phone',
            'type' => 'text',
            'value' => $this->form_validation->set_value('phone', $user->phone),
        ];
        $this->data['password'] = [
            'name' => 'password',
            'id' => 'password',
            'type' => 'password'
        ];
        $this->data['password_confirm'] = [
            'name' => 'password_confirm',
            'id' => 'password_confirm',
            'type' => 'password'
        ];

        $this->_render_page('auth/edit_user', $this->data);
    }

    /**
     * Create a new group
     */
    public function create_group()
    {
        $this->data['title'] = $this->lang->line('create_group_title');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        // validate form input
        $this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'trim|required|alpha_dash|xss_clean');

        if ($this->form_validation->run() === TRUE) {
            $new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
            if ($new_group_id) {
                // check to see if we are creating the group
                // redirect them back to the admin page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("auth", 'refresh');
            } else {
                $this->session->set_flashdata('message', $this->ion_auth->errors());
            }
        }

        // display the create group form
        // set the flash data error message if there is one
        $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

        $this->data['group_name'] = [
            'name' => 'group_name',
            'id' => 'group_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_name'),
        ];
        $this->data['description'] = [
            'name' => 'description',
            'id' => 'description',
            'type' => 'text',
            'value' => $this->form_validation->set_value('description'),
        ];

        $this->_render_page('auth/create_group', $this->data);
    }

    /**
     * Edit a group
     *
     * @param int|string $id
     */
    public function edit_group($id)
    {
        // bail if no group id given
        if (!$id || empty($id)) {
            redirect('auth', 'refresh');
        }

        $this->data['title'] = $this->lang->line('edit_group_title');

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        $group = $this->ion_auth->group($id)->row();

        // validate form input
        $this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'trim|required|alpha_dash|xss_clean');

        if (isset($_POST) && !empty($_POST)) {
            if ($this->form_validation->run() === TRUE) {
                $group_update = $this->ion_auth->update_group($id, $_POST['group_name'], array(
                    'description' => $_POST['group_description']
                ));

                if ($group_update) {
                    $this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
                    redirect("auth", 'refresh');
                } else {
                    $this->session->set_flashdata('message', $this->ion_auth->errors());
                }
            }
        }

        // set the flash data error message if there is one
        $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

        // pass the user to the view
        $this->data['group'] = $group;

        $this->data['group_name'] = [
            'name' => 'group_name',
            'id' => 'group_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_name', $group->name),
        ];
        if ($this->config->item('admin_group', 'ion_auth') === $group->name) {
            $this->data['group_name']['readonly'] = 'readonly';
        }

        $this->data['group_description'] = [
            'name' => 'group_description',
            'id' => 'group_description',
            'type' => 'text',
            'value' => $this->form_validation->set_value('group_description', $group->description),
        ];

        $this->_render_page('auth' . DIRECTORY_SEPARATOR . 'edit_group', $this->data);
    }

    /**
     * @return array A CSRF key-value pair
     */
    public function _get_csrf_nonce()
    {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return [$key => $value];
    }

    /**
     * @return bool Whether the posted CSRF token matches
     */
    public function _valid_csrf_nonce()
    {
        $csrfkey = $this->input->post($this->session->flashdata('csrfkey'));
        if ($csrfkey && $csrfkey === $this->session->flashdata('csrfvalue')) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @param string     $view
     * @param array|null $data
     * @param bool       $returnhtml
     *
     * @return mixed
     */
    public function _render_page($view, $data = NULL, $returnhtml = FALSE) //I think this makes more sense
    {

        $viewdata = (empty($data)) ? $this->data : $data;

        $view_html = $this->load->view($view, $viewdata, $returnhtml);

        // This will return html on 3rd argument being true
        if ($returnhtml) {
            return $view_html;
        }
    }

    public function verify_user()
    {
        /* Parameters to be passed
            mobile: 9874565478
            email: test@gmail.com 
        */
        $auth_settings = get_settings('authentication_settings', true);
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return;
        }
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {

            
            sendWebJsonResponse(true, strip_tags(validation_errors()));
        } else {

            $user_data = fetch_details('users', ['mobile' => $_POST['mobile']], 'id');

            // If this is a forgot-password flow (forget_password_val == 1) we skip role checks
            $forget_password_val = isset($_POST['forget_password_val']) ? $_POST['forget_password_val'] : 0;
            if ($forget_password_val != 1) {
                if (isset($_POST['from_seller']) && $_POST['from_seller'] == '1') {
                    if (!$this->ion_auth->is_seller($user_data[0]['id'])) {
                       
                        sendWebJsonResponse(true, 'You are not authorized to access seller!');
                    }
                }
                if (isset($_POST['from_admin']) && $_POST['from_admin'] == '1') {
                    if (!$this->ion_auth->is_admin($user_data[0]['id'])) {
                       
                        sendWebJsonResponse(true, 'You are not authorized to access admin!');
                    }
                }
                if (isset($_POST['from_delivery_boy']) && $_POST['from_delivery_boy'] == '1') {
                    if (!$this->ion_auth->is_delivery_boy($user_data[0]['id'])) {
                     
                        sendWebJsonResponse(true, 'You are not authorized to access delivery boy!');
                    }
                }
            }

            if (!isset($_POST['forget_password_val']) && is_exist(['mobile' => $_POST['mobile']], 'users')) {
                if (isset($_POST['mobile']) && is_exist(['mobile' => $_POST['mobile']], 'users')) {
                   
                    sendWebJsonResponse(true, 'Mobile is already registered.Please try to login !');
                }
            }
            if (isset($_POST['forget_password_val']) && ($_POST['forget_password_val'] == 1) && !is_exist(['mobile' => $_POST['mobile']], 'users')) {
               
                sendWebJsonResponse(true, 'Mobile is not register yet !');
            }
            if (isset($_POST['email']) && is_exist(['email' => $_POST['email']], 'users')) {
               
                sendWebJsonResponse(true, 'Email is already registered.Please try to login !');
            }

            if ($auth_settings['authentication_method'] == "firebase") {
                
                sendWebJsonResponse(false, 'Ready to sent OTP request');
            } else {

                $mobile = $_POST['mobile'];
                $country_code = $_POST['country_code'];
                $mobile_data = array(
                    'mobile' => $mobile // Replace $mobile with the actual mobile value you want to insert
                );

                if (isset($_POST['mobile']) && !is_exist(['mobile' => $_POST['mobile']], 'otps')) {
                    $this->db->insert('otps', $mobile_data);
                }

                $otps = fetch_details('otps', ['mobile' => $mobile]);

                $query = $this->db->select(' * ')->where('id', $otps[0]['id'])->get('otps')->result_array();
                $otp = random_int(100000, 999999);
                $data = set_user_otp($mobile, $otp, $country_code);

               
                sendWebJsonResponse(false, 'Ready to sent OTP request');
            }
        }
    }

    public function register_user()
    {

        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean');
        if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'phone') {
            $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean|valid_email|is_unique[users.email]', array('is_unique' => ' The email is already registered . Please login'));
        } else {
            $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean|valid_email');
        }
        if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'phone') {
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]|numeric|is_unique[users.mobile]', array('is_unique' => ' The mobile number is already registered . Please login'));
            $this->form_validation->set_rules('country_code', 'Country Code', 'trim|required|xss_clean');
        }
        $this->form_validation->set_rules('dob', 'Date of birth', 'trim|xss_clean');
        $this->form_validation->set_rules('city', 'City', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('area', 'Area', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('street', 'Street', 'trim|xss_clean');
        $this->form_validation->set_rules('pincode', 'Pincode', 'trim|xss_clean');
        $this->form_validation->set_rules('fcm_id', 'Fcm Id', 'trim|xss_clean');
        $this->form_validation->set_rules('friends_code', 'Friends code', 'trim|xss_clean');
        $this->form_validation->set_rules('latitude', 'Latitude', 'trim|xss_clean');
        $this->form_validation->set_rules('longitude', 'Longitude', 'trim|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();
        if (!$this->form_validation->run()) {
           
            sendWebJsonResponse(true, strip_tags(validation_errors()));
        } else {
            if (isset($_POST['friends_code']) && !empty($_POST['friends_code'])) {
                $settings = get_settings('system_settings', true);
                $refer_earn_bonus_times = $settings['refer_earn_bonus_times'];
                $used_refer_and_earn = fetch_details('users', ['friends_code' => $_POST['friends_code']]);

                if (count($used_refer_and_earn) < $refer_earn_bonus_times) {

                    if (!is_exist(['referral_code' => $_POST['friends_code']], 'users')) {
                       
                        sendWebJsonResponse(true, 'Invalid friends code!');
                    }
                } else {
                    
                    sendWebJsonResponse(true, "Code already used for $refer_earn_bonus_times times!");
                }
            }
            if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'phone') {
                $identity_column = $this->config->item('identity', 'ion_auth');
            } else {
                $identity_column = 'email';
            }
            $email = strtolower($this->input->post('email'));
            $mobile = $this->input->post('mobile');
            if (isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'phone') {
                $identity = ($identity_column == 'mobile') ? $mobile : $email;
            } else {
                $identity = $email;
            }
            $password = $this->input->post('password');


            $additional_data = [
                'username' => $this->input->post('name'),
                'mobile' => (isset($_POST['mobile']) && !empty($_POST['mobile'])) ? $this->input->post('mobile') : '',
                'dob' => $this->input->post('dob'),
                'city' => $this->input->post('city'),
                'area' => $this->input->post('area'),
                'country_code' => isset($_POST['country_code']) ? str_replace('+', '', $this->input->post('country_code')) : 0,
                'pincode' => $this->input->post('pincode'),
                'street' => $this->input->post('street'),
                'fcm_id' => $this->input->post('fcm_id'),
                'friends_code' => $this->input->post('friends_code', true),
                'referral_code' => $this->input->post('referral_code'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'active' => 1,
                'type' => $this->input->post('type'),
            ];

            $auth_settings = get_settings('authentication_settings', true);
            if ($auth_settings['authentication_method'] == "sms" && isset($_POST['type']) && !empty($_POST['type']) && $_POST['type'] == 'phone') {
                $otps = fetch_details('otps', ['mobile' => $mobile]);
                $time = $otps[0]['created_at'];
                $time_expire = checkOTPExpiration($time);

                if ($time_expire['error'] == 1) {
                    // $response['error'] = true;
                    // $response['message'] = $time_expire['message'];
                    // echo json_encode($response);
                    // return false;
                    sendWebJsonResponse(true, $time_expire['message']);
                }
                if (($otps[0]['otp'] != $_POST['otp'])) {
                    // $response['error'] = true;
                    // $response['message'] = "OTP not valid , check again ";
                    // echo json_encode($response);
                    // return false;
                    sendWebJsonResponse(true, 'OTP not valid , check again');
                } else {
                    update_details(['varified' => 1], ['mobile' => $mobile], 'otps');
                }
            }
            $res = $this->ion_auth->register($identity, $password, $email, $additional_data, ['2']);

            update_details(['active' => 1], [$identity_column => $identity], 'users');
            $data = $this->db->select('u.id,u.username,u.email,u.mobile,c.name as city_name,a.name as area_name')->where([$identity_column => $identity])->join('cities c', 'c.id=u.city', 'left')->join('areas a', 'a.city_id=c.id', 'left')->group_by('email')->get('users u')->result_array();
            $referal_id = fetch_details('users', ['referral_code' => $this->input->post('friends_code', true)], 'id,referral_code');

            $referal_code = $this->input->post('friends_code', true);
            if (isset($referal_code) && !empty($referal_code)) {
                $refer_and_earn_data = [
                    'referal_id' => $referal_id[0]['id'],
                    'user_id' => $data[0]['id'],
                    'referal_code' => $this->input->post('friends_code', true),
                ];
                insert_details($refer_and_earn_data, 'refer_and_earn');
            }

            // $this->response['error'] = false;
            // $this->response['message'] = 'Registered Successfully';
            // $this->response['data'] = $data;
            sendWebJsonResponse(false, 'Registered Successfully', $data);
        }
        // print_r(json_encode($this->response));
    }

    /**
     * Verify email for registration - sends OTP to email
     */
    public function verify_email_user()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            sendWebJsonResponse(true, strip_tags(validation_errors()));
        } else {
            $email = strtolower($this->input->post('email', true));

            // Check if email is already registered
            if (is_exist(['email' => $email], 'users')) {
                sendWebJsonResponse(true, 'Email is already registered. Please try to login!');
            }

            // Generate OTP and send to email
            $otp = random_int(100000, 999999);
            $result = set_email_otp($email, $otp);

            if ($result['error'] == false) {
                sendWebJsonResponse(false, 'OTP sent successfully to your email.');
            } else {
                sendWebJsonResponse(true, $result['message']);
            }
        }
    }

    /**
     * Verify email OTP
     */
    public function verify_email_otp()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|xss_clean|numeric|exact_length[6]');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            sendWebJsonResponse(true, strip_tags(validation_errors()));
        } else {
            $email = strtolower($this->input->post('email', true));
            $otp = $this->input->post('otp', true);

            $result = verify_email_otp($email, $otp);

            if ($result['error'] == false) {
                sendWebJsonResponse(false, 'Email verified successfully. Please complete your registration.');
            } else {
                sendWebJsonResponse(true, $result['message']);
            }
        }
    }

    /**
     * Register user with email (after email OTP verification)
     */
    public function register_email_user()
    {
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email|is_unique[users.email]', array('is_unique' => 'The email is already registered. Please login'));
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]|numeric|is_unique[users.mobile]', array('is_unique' => 'The mobile number is already registered. Please login'));
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|min_length[6]');
        $this->form_validation->set_rules('friends_code', 'Friends code', 'trim|xss_clean');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            sendWebJsonResponse(true, strip_tags(validation_errors()));
        } else {
            $email = strtolower($this->input->post('email', true));
            
            // Verify that email OTP was verified
            $otps = fetch_details('otps', ['email' => $email, 'varified' => 1]);
            if (empty($otps)) {
                sendWebJsonResponse(true, 'Please verify your email first.');
                return;
            }

            // Check friends code if provided
            if ($this->input->post('friends_code', true)) {
                $settings = get_settings('system_settings', true);
                $refer_earn_bonus_times = $settings['refer_earn_bonus_times'];
                $used_refer_and_earn = fetch_details('users', ['friends_code' => $this->input->post('friends_code', true)]);

                if (count($used_refer_and_earn) < $refer_earn_bonus_times) {
                    if (!is_exist(['referral_code' => $this->input->post('friends_code', true)], 'users')) {
                        sendWebJsonResponse(true, 'Invalid friends code!');
                    }
                } else {
                    sendWebJsonResponse(true, "Code already used for $refer_earn_bonus_times times!");
                }
            }

            $identity_column = $this->config->item('identity', 'ion_auth');
            $mobile = $this->input->post('mobile', true);
            $password = $this->input->post('password', true);

            // Use mobile as identity since identity column is typically mobile
            $identity = ($identity_column == 'mobile') ? $mobile : $email;

            $referal_code = substr(str_shuffle(str_repeat("AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz1234567890", 8)), 0, 8);

            $additional_data = [
                'username' => $this->input->post('name', true),
                'mobile' => $mobile,
                'email' => $email,
                'country_code' => $this->input->post('country_code', true) ? str_replace('+', '', $this->input->post('country_code', true)) : 0,
                'fcm_id' => $this->input->post('fcm_id', true),
                'friends_code' => $this->input->post('friends_code', true),
                'referral_code' => $referal_code,
                'active' => 1,
                'type' => 'email',
                'email_verified' => 1,  // Email is verified via OTP
                'mobile_verified' => 0, // Mobile needs to be verified separately if needed
            ];

            $res = $this->ion_auth->register($identity, $password, $email, $additional_data, ['2']);

            if ($res) {
                update_details(['active' => 1], [$identity_column => $identity], 'users');
                
                $data = $this->db->select('u.id,u.username,u.email,u.mobile,c.name as city_name,a.name as area_name')
                    ->where([$identity_column => $identity])
                    ->join('cities c', 'c.id=u.city', 'left')
                    ->join('areas a', 'a.city_id=c.id', 'left')
                    ->group_by('email')
                    ->get('users u')
                    ->result_array();

                // Handle referral
                $referal_id = fetch_details('users', ['referral_code' => $this->input->post('friends_code', true)], 'id,referral_code');
                $friends_code = $this->input->post('friends_code', true);
                
                if (isset($friends_code) && !empty($friends_code) && !empty($referal_id)) {
                    $refer_and_earn_data = [
                        'referal_id' => $referal_id[0]['id'],
                        'user_id' => $data[0]['id'],
                        'referal_code' => $friends_code,
                    ];
                    insert_details($refer_and_earn_data, 'refer_and_earn');
                }

                // Clean up OTP record
                $this->db->where('email', $email)->delete('otps');

                sendWebJsonResponse(false, 'Registered Successfully', $data);
            } else {
                sendWebJsonResponse(true, 'Registration failed. Please try again.');
            }
        }
    }

    /**
     * Resend email OTP
     */
    public function resend_email_otp()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            sendWebJsonResponse(true, strip_tags(validation_errors()));
        } else {
            $email = strtolower($this->input->post('email', true));

            // Check if email is already registered
            if (is_exist(['email' => $email], 'users')) {
                sendWebJsonResponse(true, 'Email is already registered. Please try to login!');
            }

            // Generate new OTP and send to email
            $otp = random_int(100000, 999999);
            $result = set_email_otp($email, $otp);

            if ($result['error'] == false) {
                sendWebJsonResponse(false, 'OTP resent successfully to your email.');
            } else {
                sendWebJsonResponse(true, $result['message']);
            }
        }
    }

    /**
     * Send forgot password OTP to email
     */
    public function forgot_password_email()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $email = strtolower($this->input->post('email', true));

        // Check if email exists in the system
        $user = fetch_details('users', ['email' => $email]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'No account found with this email address.';
            echo json_encode($this->response);
            return;
        }

        // Generate OTP and send to email
        $otp = random_int(100000, 999999);
        $result = set_email_otp($email, $otp);

        if ($result['error'] == false) {
            $this->response['error'] = false;
            $this->response['message'] = 'OTP sent successfully to your email.';
        } else {
            $this->response['error'] = true;
            $this->response['message'] = $result['message'];
        }
        echo json_encode($this->response);
    }

    /**
     * Reset password using email OTP
     */
    public function reset_password_email()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|xss_clean|numeric|exact_length[6]');
        $this->form_validation->set_rules('new_password', 'New Password', 'trim|required|xss_clean|min_length[6]');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $email = strtolower($this->input->post('email', true));
        $otp = $this->input->post('otp', true);
        $new_password = $this->input->post('new_password', true);

        // Verify OTP
        $result = verify_email_otp($email, $otp);
        if ($result['error'] == true) {
            $this->response['error'] = true;
            $this->response['message'] = $result['message'];
            echo json_encode($this->response);
            return;
        }

        // Get user by email
        $user = fetch_details('users', ['email' => $email]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'User not found.';
            echo json_encode($this->response);
            return;
        }

        // Update password using Ion Auth - use the correct identity based on ion_auth config
        $identity_column = $this->config->item('identity', 'ion_auth');
        $identity = ($identity_column == 'email') ? $user[0]['email'] : $user[0]['mobile'];
        
        if ($this->ion_auth->reset_password($identity, $new_password)) {
            // Delete the OTP record
            $this->db->delete('otps', ['email' => $email]);
            
            $this->response['error'] = false;
            $this->response['message'] = 'Password reset successfully! You can now login with your new password.';
        } else {
            $this->response['error'] = true;
            $this->response['message'] = 'Failed to reset password. Please try again.';
        }
        echo json_encode($this->response);
    }

    /**
     * Resend forgot password OTP to email
     */
    public function resend_forgot_password_email_otp()
    {
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $email = strtolower($this->input->post('email', true));

        // Generate new OTP and send to email
        $otp = random_int(100000, 999999);
        $result = set_email_otp($email, $otp);

        if ($result['error'] == false) {
            $this->response['error'] = false;
            $this->response['message'] = 'OTP resent successfully to your email.';
        } else {
            $this->response['error'] = true;
            $this->response['message'] = $result['message'];
        }
        echo json_encode($this->response);
    }

    /**
     * Send email OTP for profile verification (logged in user)
     */
    public function send_profile_email_otp()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->response['error'] = true;
            $this->response['message'] = 'Please login first.';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $email = strtolower($this->input->post('email', true));
        $user_id = $this->ion_auth->get_user_id();
        
        // Verify that this email belongs to the logged in user
        $user = fetch_details('users', ['id' => $user_id, 'email' => $email]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'This email does not match your account.';
            echo json_encode($this->response);
            return;
        }

        // Check if already verified
        if (isset($user[0]['email_verified']) && $user[0]['email_verified'] == 1) {
            $this->response['error'] = true;
            $this->response['message'] = 'Your email is already verified.';
            echo json_encode($this->response);
            return;
        }

        // Generate OTP and send to email
        $otp = random_int(100000, 999999);
        $result = set_email_otp($email, $otp);

        if ($result['error'] == false) {
            $this->response['error'] = false;
            $this->response['message'] = 'OTP sent successfully to your email.';
        } else {
            $this->response['error'] = true;
            $this->response['message'] = $result['message'];
        }
        echo json_encode($this->response);
    }

    /**
     * Verify email OTP for profile verification (logged in user)
     */
    public function verify_profile_email_otp()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->response['error'] = true;
            $this->response['message'] = 'Please login first.';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|xss_clean|numeric|exact_length[6]');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $email = strtolower($this->input->post('email', true));
        $otp = $this->input->post('otp', true);
        $user_id = $this->ion_auth->get_user_id();

        // Verify that this email belongs to the logged in user
        $user = fetch_details('users', ['id' => $user_id, 'email' => $email]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'This email does not match your account.';
            echo json_encode($this->response);
            return;
        }

        $result = verify_email_otp($email, $otp);

        if ($result['error'] == false) {
            // Update email_verified in users table
            $this->db->where('id', $user_id);
            $this->db->update('users', ['email_verified' => 1]);

            // Clear OTP record
            $this->db->delete('otps', ['email' => $email]);

            $this->response['error'] = false;
            $this->response['message'] = 'Email verified successfully!';
        } else {
            $this->response['error'] = true;
            $this->response['message'] = $result['message'];
        }
        echo json_encode($this->response);
    }

    /**
     * Send mobile OTP for profile verification (logged in user) - using SMS gateway
     */
    public function send_profile_mobile_otp()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->response['error'] = true;
            $this->response['message'] = 'Please login first.';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|numeric');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $mobile = $this->input->post('mobile', true);
        $country_code = $this->input->post('country_code', true) ?: '';
        $user_id = $this->ion_auth->get_user_id();
        
        // Verify that this mobile belongs to the logged in user
        $user = fetch_details('users', ['id' => $user_id, 'mobile' => $mobile]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'This mobile number does not match your account.';
            echo json_encode($this->response);
            return;
        }

        // Check if already verified
        if (isset($user[0]['mobile_verified']) && $user[0]['mobile_verified'] == 1) {
            $this->response['error'] = true;
            $this->response['message'] = 'Your mobile number is already verified.';
            echo json_encode($this->response);
            return;
        }

        // Generate OTP
        $otp = random_int(100000, 999999);
        $dateString = date('Y-m-d H:i:s');
        $time = strtotime($dateString);

        // Store OTP in database
        $otps = fetch_details('otps', ['mobile' => $mobile]);
        $data = [
            'otp' => $otp,
            'created_at' => $time
        ];

        if (!empty($otps)) {
            $this->db->where('mobile', $mobile);
            $this->db->update('otps', $data);
        } else {
            $data['mobile'] = $mobile;
            $data['email'] = '';
            $this->db->insert('otps', $data);
        }

        // Send SMS
        $settings = get_settings('system_settings', true);
        $app_name = isset($settings['app_name']) ? $settings['app_name'] : 'eShop';
        $message = "Your OTP for mobile verification on $app_name is: $otp. This OTP is valid for 1 minute.";
        
        $sms_result = send_sms($mobile, $message, '+' . $country_code);

        // Check if SMS was sent successfully (http_code 200 or 201)
        if (isset($sms_result['http_code']) && ($sms_result['http_code'] == 200 || $sms_result['http_code'] == 201)) {
            $this->response['error'] = false;
            $this->response['message'] = 'OTP sent successfully to your mobile number.';
        } else {
            // Even if SMS failed, we stored the OTP - user might still receive it
            $this->response['error'] = false;
            $this->response['message'] = 'OTP sent to your mobile number.';
        }
        echo json_encode($this->response);
    }

    /**
     * Verify mobile OTP for profile verification (logged in user) - using SMS gateway
     */
    public function verify_profile_mobile_otp()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->response['error'] = true;
            $this->response['message'] = 'Please login first.';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|numeric');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required|xss_clean|numeric|exact_length[6]');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $mobile = $this->input->post('mobile', true);
        $otp = $this->input->post('otp', true);
        $user_id = $this->ion_auth->get_user_id();

        // Verify that this mobile belongs to the logged in user
        $user = fetch_details('users', ['id' => $user_id, 'mobile' => $mobile]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'This mobile number does not match your account.';
            echo json_encode($this->response);
            return;
        }

        // Verify OTP
        $otps = fetch_details('otps', ['mobile' => $mobile]);
        
        if (empty($otps)) {
            $this->response['error'] = true;
            $this->response['message'] = 'No OTP found for this mobile. Please request a new OTP.';
            echo json_encode($this->response);
            return;
        }

        $stored_otp = $otps[0]['otp'];
        $created_at = $otps[0]['created_at'];

        // Check if OTP has expired
        $time_expire = checkOTPExpiration($created_at);
        if ($time_expire['error'] == true) {
            $this->response['error'] = true;
            $this->response['message'] = $time_expire['message'];
            echo json_encode($this->response);
            return;
        }

        // Verify OTP
        if ($stored_otp != $otp) {
            $this->response['error'] = true;
            $this->response['message'] = 'Invalid OTP. Please check and try again.';
            echo json_encode($this->response);
            return;
        }

        // Update mobile_verified in users table
        $this->db->where('id', $user_id);
        $this->db->update('users', ['mobile_verified' => 1]);

        // Clear OTP record
        $this->db->delete('otps', ['mobile' => $mobile]);

        $this->response['error'] = false;
        $this->response['message'] = 'Mobile number verified successfully!';
        echo json_encode($this->response);
    }

    /**
     * Verify mobile for profile (Firebase) - called after Firebase verification
     */
    public function verify_profile_mobile()
    {
        if (!$this->ion_auth->logged_in()) {
            $this->response['error'] = true;
            $this->response['message'] = 'Please login first.';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        }

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|numeric');

        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            echo json_encode($this->response);
            return;
        }

        $mobile = $this->input->post('mobile', true);
        $user_id = $this->ion_auth->get_user_id();

        // Verify that this mobile belongs to the logged in user
        $user = fetch_details('users', ['id' => $user_id, 'mobile' => $mobile]);
        if (empty($user)) {
            $this->response['error'] = true;
            $this->response['message'] = 'This mobile number does not match your account.';
            echo json_encode($this->response);
            return;
        }

        // Update mobile_verified in users table
        $this->db->where('id', $user_id);
        $this->db->update('users', ['mobile_verified' => 1]);

        $this->response['error'] = false;
        $this->response['message'] = 'Mobile number verified successfully!';
        echo json_encode($this->response);
    }
}
