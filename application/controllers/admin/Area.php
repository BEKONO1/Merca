<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Area extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper', 'file']);
        $this->load->model(['Area_model', 'Setting_model']);


        if (!has_permissions('read', 'area') || !has_permissions('read', 'city') || !has_permissions('read', 'zipcodes')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        } else {
            $this->session->set_flashdata('authorize_flag', "");
        }
    }

    public function manage_areas()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'area')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-area';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Area Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Area Management  | ' . $settings['app_name'];
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details('areas', ['id' => $_GET['edit_id']]);
            }
            $this->data['city'] = fetch_details('cities', '');
            $this->data['zipcodes'] = fetch_details('zipcodes', '');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function view_area()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Area_model->get_list($table = 'areas');
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function manage_countries()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-countries';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Countries Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Countries Management  | ' . $settings['app_name'];
            $this->data['countries'] = fetch_details('countries', '');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function country_list()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Area_model->get_countries_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function get_cities()
    {
        $search = $this->input->get('search');
        $limit = (isset($_GET['limit'])) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_GET['offset'])) ? $this->input->post('offset', true) : 0;
        $search = (isset($_GET['search'])) ? $_GET['search'] : null;
        $seller_id = (isset($_GET['seller_id'])) ? $_GET['seller_id'] : null;

        // If no search term, return 5 preloaded cities
        if (empty($search) || trim($search) === '') {
            $response = $this->Area_model->get_cities_list('', 5, 0, $seller_id);
        } else {
            $response = $this->Area_model->get_cities_list($search, $limit, $offset, $seller_id);
        }
        echo json_encode($response);
    }


    public function get_zipcode_list()
    {
        $search = $this->input->get('search');
        $response = $this->Area_model->get_zipcode($search);
        echo json_encode($response);
    }
    public function add_area()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (isset($_POST['edit_area'])) {
                if (print_msg(!has_permissions('update', 'area'), PERMISSION_ERROR_MSG, 'area')) {
                    return false;
                }
            } else {
                if (print_msg(!has_permissions('create', 'area'), PERMISSION_ERROR_MSG, 'area')) {
                    return false;
                }
            }

            $this->form_validation->set_rules('area_name', ' Area Name ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('city', ' City ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('zipcode', ' Zipcode ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('minimum_free_delivery_order_amount', ' Minimum Free Delivery Amount ', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('delivery_charges', ' Delivery Charges ', 'trim|required|numeric|xss_clean');

            if (!$this->form_validation->run()) {
                sendWebJsonResponse(true, strip_tags(validation_errors()));
            } else {
                if (isset($_POST['edit_area'])) {
                    if (is_exist(['name' => $_POST['area_name'], 'city_id' => $_POST['city'], 'zipcode_id' => $_POST['zipcode']], 'areas', $_POST['edit_area'])) {
                        $message = "Combination Already Exist ! Provide a unique Combination";
                        sendWebJsonResponse(true, $message);
                    }
                } else {
                    if (is_exist(['name' => $_POST['area_name'], 'city_id' => $_POST['city'], 'zipcode_id' => $_POST['zipcode']], 'areas')) {
                        $message = "Combination Already Exist ! Provide a unique Combination";
                        sendWebJsonResponse(true, $message);
                    }
                }

                $this->Area_model->add_area($_POST);

                $message = (isset($_POST['edit_area'])) ? 'Area Updated Successfully' : 'Area Added Successfully';
                sendWebJsonResponse(false, $message);
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function bulk_update()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->form_validation->set_rules('city', ' City ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('bulk_update_minimum_free_delivery_order_amount', ' Minimum Free Delivery Amount ', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('bulk_update_delivery_charges', ' Delivery Charges ', 'trim|required|numeric|xss_clean');

            if (!$this->form_validation->run()) {
                sendWebJsonResponse(true, strip_tags(validation_errors()));
            } else {
                $this->Area_model->bulk_edit_area($_POST);

                sendWebJsonResponse(false, 'Delivery Charge Updated Successfully');
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_cities()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'city')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-city';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'City Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' City Management  | ' . $settings['app_name'];
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details('cities', ['id' => $_GET['edit_id']]);
            }
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Area_model->get_list($table = 'cities');
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function delete_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            delete_details(['zipcode_id' => $_GET['id']], 'areas');

            if (delete_details(['id' => $_GET['id']], 'cities')) {
                $response['error'] = false;
                $response['message'] = 'City Deleted Successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something went wrong';
            }
            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (isset($_POST['edit_city'])) {
                if (print_msg(!has_permissions('update', 'city'), PERMISSION_ERROR_MSG, 'city')) {
                    return false;
                }
            } else {
                if (print_msg(!has_permissions('create', 'city'), PERMISSION_ERROR_MSG, 'city')) {
                    return false;
                }
            }
            if ($this->input->post('edit_city') == null) {
                $this->form_validation->set_rules('city_name', ' City Name ', 'trim|required|is_unique[cities.name]|xss_clean', array('is_unique' => ' The ' . $_POST['city_name'] . ' city is already added.'));
            }
            $this->form_validation->set_rules('minimum_free_delivery_order_amount', ' Minimum Free Delivery Amount ', 'trim|required|numeric|xss_clean|greater_than_equal_to[0]');
            $this->form_validation->set_rules('delivery_charges', ' Delivery Charges ', 'trim|required|numeric|xss_clean|greater_than_equal_to[0]');

            if (!$this->form_validation->run()) {
                sendWebJsonResponse(true, strip_tags(validation_errors()));
            } else {
                $edit_city = $this->input->post('edit_city', true);
                $city_name = $this->input->post('city_name', true);
                if (!isset($edit_city)) {
                    if (is_exist(['name' => $city_name], 'cities')) {
                        sendWebJsonResponse(true, "City Name Already Exist ! Provide a unique name");
                    }
                }
                if ($this->input->post('edit_city') != null) {
                    $city_name = $this->input->post('city_name');
                    unset($city_name);
                }

                $fields = [
                    'edit_city',
                    'city_name',
                    'minimum_free_delivery_order_amount',
                    'delivery_charges'
                ];

                foreach ($fields as $field) {
                    $city_data[$field] = $this->input->post($field, true) ?? "";
                }
                $this->Area_model->add_city($city_data);

                $message = (isset($edit_city) && !empty($edit_city)) ? 'City Updated Successfully' : 'City Added Successfully';
                sendWebJsonResponse(false, $message);
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    // manage zipcodes

    public function manage_zipcodes()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'zipcodes')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-zipcodes';
            $settings = get_settings('system_settings', true);
            $shipping_method = get_settings('shipping_method', true);
            $default_zipcode_detail = get_settings('default_zipcode_detail', true);
            $this->data['title'] = 'Zipcodes Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Zipcode Management  | ' . $settings['app_name'];
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details('zipcodes', ['id' => $_GET['edit_id']]);
            }
            $this->data['city'] = fetch_details('cities', '');
            $this->data['settings'] = $settings;
            $this->data['shipping_method'] = $shipping_method;
            $this->data['default_zipcode_detail'] = $default_zipcode_detail;
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_zipcodes()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            return $this->Area_model->get_zipcode_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function get_zipcodes()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            $limit = (isset($_GET['limit'])) ? $this->input->post('limit', true) : 30;
            $offset = (isset($_GET['offset'])) ? $this->input->post('offset', true) : 0;
            $search = (isset($_GET['search'])) ? $_GET['search'] : null;
            $seller_id = (isset($_GET['seller_id'])) ? $_GET['seller_id'] : null;
            $zipcodes = $this->Area_model->get_zipcodes($search, $limit, $offset, $seller_id, 1);
            $this->response = $zipcodes['data'];
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_zipcode()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (isset($_POST['edit_zipcode'])) {
                if (print_msg(!has_permissions('update', 'zipcodes'), PERMISSION_ERROR_MSG, 'zipcodes')) {
                    return false;
                }
            } else {
                if (print_msg(!has_permissions('create', 'zipcodes'), PERMISSION_ERROR_MSG, 'zipcodes')) {
                    return false;
                }
            }

            $this->form_validation->set_rules('city', ' City ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('minimum_free_delivery_order_amount', ' Minimum Free Delivery Amount ', 'trim|required|numeric|xss_clean|greater_than_equal_to[0]');
            $this->form_validation->set_rules('delivery_charges', ' Delivery Charges ', 'trim|required|numeric|xss_clean|greater_than_equal_to[0]');

            $shipping_settings = get_settings('shipping_method', true);

            if (isset($shipping_settings['pincode_wise_deliverability']) && $shipping_settings['pincode_wise_deliverability'] == 1) {
                $this->form_validation->set_rules('zipcode', ' Zipcode ', 'trim|required|xss_clean');
            }
            if (!$this->form_validation->run()) {
                sendWebJsonResponse(true, strip_tags(validation_errors()));

            } else {
                $edit_zipcode = $this->input->post('edit_zipcode', true);
                $zipcode = $this->input->post('zipcode', true);
                $city = $this->input->post('city', true);
                if (isset($shipping_settings['pincode_wise_deliverability']) && $shipping_settings['pincode_wise_deliverability'] == 1) {
                    if (isset($edit_zipcode)) {
                        if (is_exist(['city_id' => $city, 'zipcode' => $zipcode], 'zipcodes', $edit_zipcode)) {
                            sendWebJsonResponse(true, 'Combination Already Exist ! Provide a unique Combination');
                        }
                    } else {
                        if (is_exist(['city_id' => $city, 'zipcode' => $zipcode], 'zipcodes')) {
                            sendWebJsonResponse(true, 'Combination Already Exist ! Provide a unique Combination');
                        }
                    }
                } else if (isset($shipping_settings['city_wise_deliverability']) && $shipping_settings['city_wise_deliverability'] == 1) {
                    if (isset($edit_zipcode)) {
                        if (is_exist(['city_id' => $city, 'zipcode' => ''], 'zipcodes', $edit_zipcode)) {
                            sendWebJsonResponse(true, 'Combination Already Exist ! Provide a unique Combination');
                        }
                    } else {
                        if (is_exist(['city_id' => $city, 'zipcode' => ''], 'zipcodes')) {
                            sendWebJsonResponse(true, 'Combination Already Exist ! Provide a unique Combination');
                        }
                    }
                }

                $fields = [
                    'zipcode',
                    'city',
                    'minimum_free_delivery_order_amount',
                    'delivery_charges',
                    'edit_zipcode'
                ];

                foreach ($fields as $field) {
                    $zipcode_data[$field] = $this->input->post($field, true) ?? "";
                }
                $this->Area_model->add_zipcode($zipcode_data);

                $message = (isset($edit_zipcode) && !empty($edit_zipcode)) ? 'Zipcode Updated Successfully' : 'Zipcode Added Successfully';

                sendWebJsonResponse(error: false, message: $message);
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_zipcode()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('delete', 'zipcodes'), PERMISSION_ERROR_MSG, 'zipcodes')) {
                return false;
            }
            delete_details(['zipcode_id' => $_GET['id']], 'areas');
            if (delete_details(['id' => $_GET['id']], 'zipcodes')) {
                $response['error'] = false;
                $response['message'] = 'Zipcode Deleted Successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something went wrong';
            }
            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_zipcode_multi()
    {
        // Check if it's an AJAX request and if IDs are sent via POST
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin() && $this->input->post('ids')) {
            $ids = $this->input->post('ids');

            $deleted = $this->Area_model->delete_zipcodes($ids);

            if ($deleted) {
                sendWebJsonResponse(false, 'Zipcode items deleted successfully.');
            } else {
                sendWebJsonResponse(true, 'Failed to delete Zipcode items.');
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function location_bulk_upload()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'location-bulk-upload';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Bulk Upload | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Bulk Upload | ' . $settings['app_name'];

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function process_bulk_upload()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('create', 'product'), PERMISSION_ERROR_MSG, 'product')) {
                return false;
            }
            $this->form_validation->set_rules('bulk_upload', '', 'xss_clean');
            $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
            $this->form_validation->set_rules('location_type', 'Location Type', 'trim|required|xss_clean');
            if (empty($_FILES['upload_file']['name'])) {
                $this->form_validation->set_rules('upload_file', 'File', 'trim|required|xss_clean', array('required' => 'Please choose file'));
            }

            if (!$this->form_validation->run()) {
                sendWebJsonResponse(true, strip_tags(validation_errors()));
            } else {
                $allowed_mime_type_arr = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv');
                $mime = get_mime_by_extension($_FILES['upload_file']['name']);
                if (!in_array($mime, $allowed_mime_type_arr)) {
                    sendWebJsonResponse(true, 'Invalid file format!');
                }
                $csv = $_FILES['upload_file']['tmp_name'];
                $temp = 0;
                $temp1 = 0;
                $handle = fopen($csv, "r");
                $allowed_status = array("received", "processed", "shipped");
                $video_types = array("youtube", "vimeo");
                $this->response['message'] = '';
                $type = $this->input->post('type', true);
                $location_type = $this->input->post('location_type', true);
                if ($type == 'upload' && $location_type == 'zipcode') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                sendWebJsonResponse(true, 'Zipcode is empty at row ' . $temp);
                            }
                            if (empty($row[1])) {
                                sendWebJsonResponse(true, 'City Id is empty at row ' . $temp);
                            }
                            if (!empty($row[1]) && $row[1] != "") {
                                if (!is_exist(['id' => $row[1]], 'cities')) {
                                    sendWebJsonResponse(true, 'City is not exist in your database at row ' . $temp);
                                }
                            }
                            if (empty($row[2])) {
                                sendWebJsonResponse(true, 'Minimum Free Delivery Order Amount is empty at row ' . $temp);
                            }
                            if (is_exist(['city_id' => $row[1], 'zipcode' => $row[0]], 'zipcodes')) {
                                sendWebJsonResponse(true, 'Combination Already Exist ! Provide a unique Combination');
                            }
                        }
                        $temp++;
                    }

                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp1 != 0) {
                            $data['zipcode'] = $row[0];
                            $data['city_id'] = $row[1];
                            $data['minimum_free_delivery_order_amount'] = $row[2];
                            $data['delivery_charges'] = $row[3];
                            $this->db->insert('zipcodes', $data);
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    sendWebJsonResponse(false, 'Zipcodes uploaded successfully!');
                } else if ($type == 'upload' && $location_type == 'city') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                sendWebJsonResponse(true, 'City Name is empty at row ' . $temp);
                            }
                        }
                        $temp++;
                    }

                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp1 != 0) {
                            $data['name'] = $row[0];
                            $this->db->insert('cities', $data);
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    sendWebJsonResponse(false, 'Cities uploaded successfully!');
                } else if ($type == 'upload' && $location_type == 'area') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp != 0) {
                            if (empty($row[0]) && $row[0] == "") {
                                sendWebJsonResponse(true, 'Area name is empty at row ' . $temp);
                            }
                            if (empty($row[1]) && $row[1] == "") {
                                sendWebJsonResponse(true, 'City id is empty at row ' . $temp);
                            }
                            if (!empty($row[1]) && $row[1] != "") {
                                if (!is_exist(['id' => $row[1]], 'cities')) {
                                    sendWebJsonResponse(true, 'City is not exist in your database at row ' . $temp);
                                }
                            }
                            if (empty($row[2]) && $row[2] == "") {
                                sendWebJsonResponse(true, 'Zipcode id is empty at row ' . $temp);
                            }
                            if (!empty($row[2]) && $row[2] != "") {
                                if (!is_exist(['id' => $row[2]], 'zipcodes')) {
                                    sendWebJsonResponse(true, 'Zipcode is not exist in your database at row ' . $temp);
                                }
                            }
                            if (is_exist(['name' => $row[0], 'city_id' => $row[1], 'zipcode_id' => $row[2]], 'areas')) {
                                sendWebJsonResponse(true, "Combination Already Exist ! Provide a unique Combination at row $temp");
                            }
                        }
                        $temp++;
                    }

                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp1 != 0) {
                            $data['name'] = $row[0];
                            $data['city_id'] = $row[1];
                            $data['zipcode_id'] = $row[2];
                            $data['minimum_free_delivery_order_amount'] = (isset($row[3]) && $row[3] != "") ? $row[3] : 100;
                            $data['delivery_charges'] = (isset($row[4]) && $row[4] != "") ? $row[4] : 0;
                            $this->db->insert('areas', $data);
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    sendWebJsonResponse(false, 'Areas uploaded successfully!');
                } else if ($type == 'update' && $location_type == 'zipcode') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                sendWebJsonResponse(true, 'Zipcode id empty at row ' . $temp);
                            }

                            if (!empty($row[0]) && $row[0] != "") {
                                if (!is_exist(['id' => $row[0]], 'zipcodes')) {
                                    sendWebJsonResponse(true, 'Zipcode id is not exist in your database at row ' . $temp);
                                }
                            }

                            if (empty($row[1])) {
                                sendWebJsonResponse(true, 'Zipcode empty at row ' . $temp);
                            }
                            if (empty($row[2])) {
                                sendWebJsonResponse(true, 'City Id is empty at row ' . $temp);
                            }
                            if (!empty($row[2]) && $row[2] != "") {
                                if (!is_exist(['id' => $row[2]], 'cities')) {
                                    sendWebJsonResponse(true, 'City is not exist in your database at row ' . $temp);
                                }
                            }
                            if (empty($row[3])) {
                                sendWebJsonResponse(true, 'Minimum Free Delivery Order Amount is empty at row ' . $temp);
                            }
                            if (is_exist(['city_id' => $row[2], 'zipcode' => $row[1]], 'zipcodes', $row[0])) {
                                sendWebJsonResponse(true, "Combination Already Exist ! Provide a unique Combination");
                            }
                        }
                        $temp++;
                    }
                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {

                        if ($temp1 != 0) {
                            $zipcode_id = $row[0];
                            $zipcode = fetch_details('zipcodes', ['id' => $zipcode_id], '*');
                            if (!empty($zipcode)) {
                                if (!empty($row[1])) {
                                    $data['zipcode'] = $row[1];
                                    $data['city_id'] = $row[2];
                                    $data['minimum_free_delivery_order_amount'] = $row[3];
                                    $data['delivery_charges'] = $row[4];
                                } else {
                                    $data['zipcode'] = $zipcode[0]['zipcode'];
                                    $data['city_id'] = $row[2];
                                    $data['minimum_free_delivery_order_amount'] = $row[3];
                                    $data['delivery_charges'] = $row[4];
                                }
                                $this->db->where('id', $zipcode_id)->update('zipcodes', $data);
                            } else {
                                sendWebJsonResponse(true, 'Zipcode id: ' . $zipcode_id . ' not exist!');
                            }
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    sendWebJsonResponse(false, 'Zipcodes updated successfully!');
                } else if ($type == 'update' && $location_type == 'city') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                sendWebJsonResponse(true, 'City id empty at row ' . $temp);
                            }

                            if (!empty($row[0]) && $row[0] != "") {
                                if (!is_exist(['id' => $row[0]], 'cities')) {
                                    sendWebJsonResponse(true, 'City id is not exist in your database at row ' . $temp);
                                }
                            }

                            if (empty($row[1])) {
                                sendWebJsonResponse(true, 'City name empty at row ' . $temp);
                            }
                        }
                        $temp++;
                    }
                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp1 != 0) {
                            $city_id = $row[0];
                            $city = fetch_details('cities', ['id' => $city_id], '*');
                            if (!empty($city)) {
                                if (!empty($row[1])) {
                                    $data['name'] = $row[1];
                                } else {
                                    $data['name'] = $city[0]['name'];
                                }
                                $this->db->where('id', $city_id)->update('cities', $data);
                            } else {
                                sendWebJsonResponse(true, 'City id: ' . $city_id . ' not exist!');

                            }
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    sendWebJsonResponse(false, 'City updated successfully!');
                } else if ($type == 'update' && $location_type == 'area') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                sendWebJsonResponse(true, 'Area id empty at row ' . $temp);
                            }

                            if (!empty($row[0]) && $row[0] != "") {
                                if (!is_exist(['id' => $row[0]], 'areas')) {
                                    sendWebJsonResponse(true, 'Area id is not exist in your database at row ' . $temp);
                                }
                            }

                            if (empty($row[1])) {
                                sendWebJsonResponse(true, 'Area name empty at row ' . $temp);
                            }
                            if (!empty($row[2]) && $row[2] != "") {
                                if (!is_exist(['id' => $row[2]], 'cities')) {
                                    sendWebJsonResponse(true, 'City is not exist in your database at row ' . $temp);
                                }
                            }
                            if (!empty($row[3]) && $row[3] != "") {
                                if (!is_exist(['id' => $row[3]], 'zipcodes')) {
                                    sendWebJsonResponse(true, 'Zipcode is not exist in your database at row ' . $temp);
                                }
                            }
                        }
                        $temp++;
                    }
                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp1 != 0) {
                            $area_id = $row[0];
                            $area = fetch_details('areas', ['id' => $area_id], '*');
                            if (!empty($area)) {
                                if (!empty($row[1])) {
                                    $data['name'] = $row[1];
                                } else {
                                    $data['name'] = $area[0]['name'];
                                }
                                if (!empty($row[2])) {
                                    $data['city_id'] = $row[2];
                                } else {
                                    $data['city_id'] = $area[0]['city_id'];
                                }
                                if (!empty($row[3])) {
                                    $data['zipcode_id'] = $row[3];
                                } else {
                                    $data['zipcode_id'] = $area[0]['zipcode_id'];
                                }
                                if (!empty($row[4])) {
                                    $data['minimum_free_delivery_order_amount'] = $row[4];
                                } else {
                                    $data['minimum_free_delivery_order_amount'] = $area[0]['minimum_free_delivery_order_amount'];
                                }
                                if (!empty($row[5])) {
                                    $data['delivery_charges'] = $row[5];
                                } else {
                                    $data['delivery_charges'] = $area[0]['delivery_charges'];
                                }
                                $this->db->where('id', $area_id)->update('areas', $data);
                            } else {
                                sendWebJsonResponse(true, 'Area id: ' . $area_id . ' not exist!');
                            }
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    sendWebJsonResponse(false, 'Area updated successfully!');
                } else {
                    sendWebJsonResponse(true, 'Invalid Type or Type Location!');
                }
            }
        }
    }

    public function table_sync()
    {
        $columns_to_check = array('city_id', 'minimum_free_delivery_order_amount', 'delivery_charges'); // Add the column names you want to check
        // check if $columns_to_check is exist in zipcodes table if not add that column
        if ($this->db->field_exists('city_id', 'zipcodes')) {
            sendWebJsonResponse(false, 'Zipcode table is already sync with Area table no need to sync it again.');
        } else {
            foreach ($columns_to_check as $column) {
                if (!$this->db->field_exists($column, 'zipcodes')) {
                    $this->add_field($column);
                }
            }
            // Get data from the area table

            $query = $this->db->select(' areas.* , cities.name as city_name , zipcodes.zipcode as zipcode')->join('cities', 'areas.city_id=cities.id')->join('zipcodes', 'areas.zipcode_id=zipcodes.id');
            $area_data = $query->get('areas')->result_array();

            if (!empty($area_data)) {
                // Process data in chunks of 500 records
                $chunks = array_chunk($area_data, 500);
                if (isset($chunks) && !empty($chunks)) {
                    foreach ($chunks as $chunk) {
                        $this->process_chunk($chunk);
                    }
                    sendWebJsonResponse(false, 'Zipcode table sync with Area table successfully.');
                } else {
                    sendWebJsonResponse(true, 'No data found for sync.');
                }
            }
        }
    }
    public function add_field($field_name)
    {
        $this->load->dbforge();
        if (isset($field_name) && $field_name == 'city_id') {
            // Add city_id field to the zipcode table

            $fields = array(
                'city_id' => array(
                    'type' => 'INT',
                    'constraint' => '11',
                    'null' => FALSE,
                    'after' => 'zipcode'
                ),
            );
        }
        if (isset($field_name) && $field_name == 'minimum_free_delivery_order_amount') {
            // Add minimum_free_delivery_order_amount field to the zipcode table

            $fields = array(
                'minimum_free_delivery_order_amount' => array(
                    'type' => 'DOUBLE',
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'city_id'
                ),
            );
        }
        if (isset($field_name) && $field_name == 'delivery_charges') {
            // Add delivery_charges field to the zipcode table

            $fields = array(
                'delivery_charges' => array(
                    'type' => 'DOUBLE',
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'minimum_free_delivery_order_amount'
                ),
            );
        }

        $this->dbforge->add_column('zipcodes', $fields);
    }

    public function process_chunk($chunk)
    {
        foreach ($chunk as $row) {
            $existing_record = $this->db->get_where('zipcodes', array('zipcode' => $row['zipcode']))->row_array();
            if ($existing_record['minimum_free_delivery_order_amount'] == 0 || $existing_record['delivery_charges'] == 0) {
                // Insert the record into the zipcode table
                $set = [
                    'minimum_free_delivery_order_amount' => $row['minimum_free_delivery_order_amount'],
                    'delivery_charges' => $row['delivery_charges'],
                    'city_id' => $row['city_id'],
                ];
                update_details($set, ['zipcode' => $row['zipcode']], 'zipcodes');
            }
        }
    }

    public function zipcode_bulk_dowload()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('create', 'product')) {
                print_msg(PERMISSION_ERROR_MSG, 'product');
                return;
            }

            $filename = 'zipcodes_' . date('Ymd') . '.csv';

            $zipcodes = $this->Area_model->get_download_zipcodes();

            $csvHeaders = [
                'zipcode id',
                'zipcode',
                'city id',
                'minimum_free_delivery_order_amount',
                'delivery_charges'
            ];

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);

            $output = fopen('php://output', 'w');
            fputcsv($output, $csvHeaders);

            foreach ($zipcodes as $zipcode) {
                $data = [$zipcode['id'], $zipcode['zipcode'], $zipcode['city_id'], $zipcode['minimum_free_delivery_order_amount'], $zipcode['delivery_charges']];
                fputcsv($output, $data);
            }

            fclose($output);
        }
    }

    public function cities_bulk_dowload()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('create', 'product')) {
                print_msg(PERMISSION_ERROR_MSG, 'product');
                return;
            }

            $filename = 'cities_' . date('Ymd') . '.csv';

            $cities = $this->Area_model->get_download_cities();

            $csvHeaders = [
                'city id',
                'city',
                'minimum_free_delivery_order_amount',
                'delivery_charges'
            ];

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);

            $output = fopen('php://output', 'w');
            fputcsv($output, $csvHeaders);

            foreach ($cities as $city) {
                $data = [$city['id'], $city['name'], $city['minimum_free_delivery_order_amount'], $city['delivery_charges']];
                fputcsv($output, $data);
            }

            fclose($output);
        }
    }

    public function countries_bulk_dowload()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('create', 'product')) {
                print_msg(PERMISSION_ERROR_MSG, 'product');
                return;
            }

            $filename = 'countries_' . date('Ymd') . '.csv';

            $countries = $this->Area_model->get_download_countries();

            $csvHeaders = [
                'country id',
                'country',
                'numeric_code',
                'phonecode',
                'capital',
                'currency',
                'currency_name',
                'currency_symbol'
            ];

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);

            $output = fopen('php://output', 'w');
            fputcsv($output, $csvHeaders);

            foreach ($countries as $country) {
                $data = [$country['id'], $country['name'], $country['numeric_code'], $country['phonecode'], $country['capital'], $country['currency'], $country['currency_name'], $country['currency_symbol']];
                fputcsv($output, $data);
            }

            fclose($output);
        }
    }

    public function manage_zipcodes_group()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'city')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-zipcodes-group';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Zipcode Group Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Zipcode Group Management  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function manage_cities_group()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'city')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-cities-group';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'City Group Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' City Group Management  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }



    public function add_zipcode_group()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $this->form_validation->set_rules('zipcode_group_name', 'Group Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('deliverable_zipcodes[]', 'Zipcodes', 'required|xss_clean');
        $this->form_validation->set_rules(
            'delivery_charges',
            'Delivery Charges',
            'trim|required|numeric|xss_clean|greater_than_equal_to[0]'
        );

        if (!$this->form_validation->run()) {
            sendWebJsonResponse(true, strip_tags(validation_errors()));
            return;
        }

        $edit_group = $this->input->post('edit_zipcode_group', true);
        $group_id = $this->input->post('update_id', true);
        $group_name = $this->input->post('zipcode_group_name', true);
        $delivery_charges = $this->input->post('delivery_charges', true);
        $zipcodes = $this->input->post('deliverable_zipcodes', true);
        $zipcodes_sorted = implode(',', array_unique($zipcodes));

        /* ----------------------------------------------------
          CHECK IF GROUP NAME EXISTS
           ---------------------------------------------------- */
        $this->db->where('group_name', $group_name);
        if ($edit_group)
            $this->db->where('id !=', $group_id);

        if ($this->db->get('zipcode_groups')->row()) {
            sendWebJsonResponse(true, 'Group name already exists.');
            return;
        }

        /* ----------------------------------------------------
           CHECK IF EXACT ZIPCODE COMBINATION EXISTS
           ---------------------------------------------------- */
        $existing = $this->db->select('group_id')
            ->from('zipcode_group_items')
            ->where_in('zipcode_id', $zipcodes)
            ->group_by('group_id')
            ->get()
            ->result_array();

        foreach ($existing as $ex) {

            $gid = $ex['group_id'];

            // Skip if editing the same group
            if ($edit_group && $gid == $group_id)
                continue;

            // Get that group's zipcodes
            $group_zips = $this->db->select('zipcode_id')
                ->where('group_id', $gid)
                ->get('zipcode_group_items')
                ->result_array();

            $group_zip_ids = array_column($group_zips, 'zipcode_id');

            // Compare sorted arrays
            $incoming = $zipcodes;
            sort($incoming);
            sort($group_zip_ids);

            if ($incoming === $group_zip_ids) {
                sendWebJsonResponse(true, 'This zipcode combination already exists in another group.');
                return;
            }
        }


        $this->db->trans_start();

        if ($edit_group) {

            $this->db->where('id', $group_id)->update('zipcode_groups', [
                'group_name' => $group_name,
                'delivery_charges' => $delivery_charges,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->delete('zipcode_group_items', ['group_id' => $group_id]);

        } else {

            $this->db->insert('zipcode_groups', [
                'group_name' => $group_name,
                'delivery_charges' => $delivery_charges,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $group_id = $this->db->insert_id();
        }

        if (!empty($zipcodes)) {
            $batch = [];
            $now = date('Y-m-d H:i:s');

            foreach ($zipcodes as $zipcode) {
                $batch[] = [
                    'group_id' => $group_id,
                    'zipcode_id' => $zipcode,
                    'created_at' => $now
                ];
            }

            $this->db->insert_batch('zipcode_group_items', $batch);
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            sendWebJsonResponse(true, 'Something went wrong. Please try again.');
            return;
        }

        sendWebJsonResponse(
            false,
            $edit_group ? 'Zipcode Group Updated Successfully' : 'Zipcode Group Added Successfully'
        );
    }



    public function view_zipcode_group()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $offset = $this->input->get('offset') ?? 0;
        $limit = $this->input->get('limit') ?? 10;
        $search = $this->input->get('search') ?? '';
        $sort = $this->input->get('sort') ?? 'id';
        $order = $this->input->get('order') ?? 'DESC';

        $this->db->from('zipcode_groups');

        if ($search) {
            $escaped = $this->db->escape_like_str($search);

            $this->db->group_start()
                ->like('group_name', $search)
                ->or_where("
                id IN (
                    SELECT group_id FROM zipcode_group_items g
                    JOIN zipcodes z ON z.id = g.zipcode_id
                    WHERE z.zipcode LIKE '%{$escaped}%'
                )
            ", null, false)
                ->group_end();
        }

        $total = $this->db->count_all_results();


        $this->db->select('
        zg.id,
        zg.group_name,
        zg.delivery_charges,
        GROUP_CONCAT(z.zipcode ORDER BY z.zipcode SEPARATOR ", ") AS zipcodes
    ');
        $this->db->from('zipcode_groups zg');
        $this->db->join('zipcode_group_items g', 'g.group_id = zg.id', 'left');
        $this->db->join('zipcodes z', 'z.id = g.zipcode_id', 'left');

        if ($search) {
            $this->db->group_start()
                ->like('zg.group_name', $search)
                ->or_like('z.zipcode', $search)
                ->group_end();
        }

        $this->db->group_by('zg.id');
        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);

        $rows = $this->db->get()->result_array();


        $data = [];
        foreach ($rows as $row) {

            $operate = '
        <div class="dropdown">
            <button class="btn btn-secondary btn-sm bg-secondary-lt" type="button" data-bs-toggle="dropdown">
                <i class="ti ti-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end table-dropdown-menu">

                <li>
                    <a class="dropdown-item editZipcodeGroup"
                       href="javascript:void(0)"
                       data-id="' . $row['id'] . '"
                       data-bs-toggle="offcanvas"
                       data-bs-target="#addZipcodeGroup">
                       <i class="ti ti-pencil me-2"></i>Edit
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item text-danger"
                       href="javascript:void(0)"
                       x-data="ajaxDelete({
                           url: base_url + \'admin/Area/delete_zipcode_group\',
                           id: \'' . $row['id'] . '\',
                           tableSelector: \'#zipcode-table\',
                           confirmTitle: \'Delete Group\',
                           confirmMessage: \'Do you really want to delete this group?\'
                       })"
                       @click="deleteItem">
                       <i class="ti ti-trash me-2"></i>Delete
                    </a>
                </li>

            </ul>
        </div>';

            $data[] = [
                'id' => $row['id'],
                'name' => $row['group_name'],
                'zipcodes' => $row['zipcodes'] ?: '-',
                'delivery_charges' => $row['delivery_charges'],
                'operate' => $operate
            ];
        }

        echo json_encode([
            'total' => $total,
            'rows' => $data
        ]);
    }



    public function get_zipcode_group($group_id)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $group = $this->db->get_where('zipcode_groups', ['id' => $group_id])->row_array();
        if (!$group) {
            echo json_encode(['error' => true, 'message' => 'Group not found']);
            return;
        }

        $zipcodes = $this->db
            ->select('zipcode_id')
            ->where('group_id', $group_id)
            ->get('zipcode_group_items')
            ->result_array();

        $group['zipcodes'] = array_column($zipcodes, 'zipcode_id');

        echo json_encode(['error' => false, 'data' => $group]);
    }


    public function delete_zipcode_group()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            sendWebJsonResponse(true, 'Unauthorized access');
            return;
        }

        $group_id = $this->input->get_post('id', true);
        if (!$group_id) {
            sendWebJsonResponse(true, 'Invalid Group ID');
            return;
        }

        if (!$this->db->where('id', $group_id)->get('zipcode_groups')->row()) {
            sendWebJsonResponse(true, 'Zipcode Group not found');
            return;
        }

        $this->db->trans_start();

        $this->db->delete('zipcode_group_items', ['group_id' => $group_id]);
        $this->db->delete('zipcode_groups', ['id' => $group_id]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            sendWebJsonResponse(true, 'Failed to delete zipcode group');
            return;
        }

        sendWebJsonResponse(false, 'Zipcode Group deleted successfully');
    }


    public function add_city_group()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $this->form_validation->set_rules('city_group_name', 'Group Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('deliverable_cities[]', 'Cities', 'required|xss_clean');
        $this->form_validation->set_rules(
            'delivery_charges',
            'Delivery Charges',
            'trim|required|numeric|xss_clean|greater_than_equal_to[0]'
        );

        if (!$this->form_validation->run()) {
            sendWebJsonResponse(true, strip_tags(validation_errors()));
            return;
        }

        $edit_group = $this->input->post('edit_city_group', true);
        $group_id = $this->input->post('update_id', true);
        $group_name = $this->input->post('city_group_name', true);
        $delivery_charges = $this->input->post('delivery_charges', true);
        $cities = $this->input->post('deliverable_cities', true);

        /* ----------------------------------------------------
           CHECK IF GROUP NAME EXISTS
           ---------------------------------------------------- */
        $this->db->where('group_name', $group_name);
        if ($edit_group)
            $this->db->where('id !=', $group_id);

        if ($this->db->get('city_groups')->row()) {
            sendWebJsonResponse(true, 'Group name already exists.');
            return;
        }

        /* ----------------------------------------------------
           CHECK IF EXACT CITY COMBINATION EXISTS
           ---------------------------------------------------- */
        $existing_groups = $this->db->select('group_id')
            ->from('city_group_items')
            ->where_in('city_id', $cities)
            ->group_by('group_id')
            ->get()->result_array();

        foreach ($existing_groups as $g) {

            $gid = $g['group_id'];

            if ($edit_group && $gid == $group_id)
                continue;

            $group_cities = $this->db->select('city_id')
                ->where('group_id', $gid)
                ->get('city_group_items')
                ->result_array();

            $group_city_ids = array_column($group_cities, 'city_id');

            $incoming = $cities;
            sort($incoming);
            sort($group_city_ids);

            if ($group_city_ids === $incoming) {
                sendWebJsonResponse(true, 'This exact city combination is already available in another group.');
                return;
            }
        }


        $this->db->trans_start();

        if ($edit_group) {

            $this->db->where('id', $group_id)->update('city_groups', [
                'group_name' => $group_name,
                'delivery_charges' => $delivery_charges,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->delete('city_group_items', ['group_id' => $group_id]);

        } else {

            $this->db->insert('city_groups', [
                'group_name' => $group_name,
                'delivery_charges' => $delivery_charges,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $group_id = $this->db->insert_id();
        }

        if (!empty($cities)) {
            $batch = [];
            $now = date('Y-m-d H:i:s');

            foreach ($cities as $city) {
                $batch[] = [
                    'group_id' => $group_id,
                    'city_id' => $city,
                    'created_at' => $now
                ];
            }

            $this->db->insert_batch('city_group_items', $batch);
        }

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            sendWebJsonResponse(true, 'Something went wrong. Please try again.');
            return;
        }

        sendWebJsonResponse(
            false,
            $edit_group ? 'City Group Updated Successfully' : 'City Group Added Successfully'
        );
    }
    public function view_city_group()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $offset = $this->input->get('offset') ?? 0;
        $limit = $this->input->get('limit') ?? 10;
        $search = $this->input->get('search') ?? '';
        $sort = $this->input->get('sort') ?? 'id';
        $order = $this->input->get('order') ?? 'DESC';

        $this->db->from('city_groups');

        if ($search) {
            $escaped = $this->db->escape_like_str($search);

            $this->db->group_start()
                ->like('group_name', $search)
                ->or_where("
                id IN (
                    SELECT group_id FROM city_group_items cgi
                    JOIN cities c ON c.id = cgi.city_id
                    WHERE c.name LIKE '%{$escaped}%'
                )
            ", null, false)
                ->group_end();
        }

        $total = $this->db->count_all_results();


        $this->db->select('
        cg.id,
        cg.group_name,
        cg.delivery_charges,
        GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ", ") AS cities
    ');
        $this->db->from('city_groups cg');
        $this->db->join('city_group_items cgi', 'cgi.group_id = cg.id', 'left');
        $this->db->join('cities c', 'c.id = cgi.city_id', 'left');

        if ($search) {
            $this->db->group_start()
                ->like('cg.group_name', $search)
                ->or_like('c.name', $search)
                ->group_end();
        }

        $this->db->group_by('cg.id');
        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);

        $rows = $this->db->get()->result_array();

        $data = [];
        foreach ($rows as $row) {

            $operate = '
        <div class="dropdown">
            <button class="btn btn-secondary btn-sm bg-secondary-lt" type="button" data-bs-toggle="dropdown">
                <i class="ti ti-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end table-dropdown-menu">

                <li>
                    <a class="dropdown-item editCityGroup"
                       href="javascript:void(0)"
                       data-id="' . $row['id'] . '"
                       data-bs-toggle="offcanvas"
                       data-bs-target="#addCityGroup">
                       <i class="ti ti-pencil me-2"></i>Edit
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item text-danger"
                       href="javascript:void(0)"
                       x-data="ajaxDelete({
                           url: base_url + \'admin/Area/delete_city_group\',
                           id: \'' . $row['id'] . '\',
                           tableSelector: \'#city-table\',
                           confirmTitle: \'Delete Group\',
                           confirmMessage: \'Do you really want to delete this group?\'
                       })"
                       @click="deleteItem">
                       <i class="ti ti-trash me-2"></i>Delete
                    </a>
                </li>

            </ul>
        </div>';

            $data[] = [
                'id' => $row['id'],
                'name' => $row['group_name'],
                'cities' => $row['cities'] ?: '-',
                'delivery_charges' => $row['delivery_charges'],
                'operate' => $operate
            ];
        }

        echo json_encode([
            'total' => $total,
            'rows' => $data
        ]);
    }

    public function get_city_group($group_id)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $group = $this->db->get_where('city_groups', ['id' => $group_id])->row_array();
        if (!$group) {
            echo json_encode(['error' => true, 'message' => 'Group not found']);
            return;
        }

        $cities = $this->db
            ->select('city_id')
            ->where('group_id', $group_id)
            ->get('city_group_items')
            ->result_array();

        $group['cities'] = array_column($cities, 'city_id');

        echo json_encode(['error' => false, 'data' => $group]);
    }



    public function delete_city_group()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            sendWebJsonResponse(true, 'Unauthorized access');
            return;
        }

        $group_id = $this->input->get_post('id', true);
        if (!$group_id) {
            sendWebJsonResponse(true, 'Invalid Group ID');
            return;
        }

        if (!$this->db->where('id', $group_id)->get('city_groups')->row()) {
            sendWebJsonResponse(true, 'City Group not found');
            return;
        }

        $this->db->trans_start();

        $this->db->delete('city_group_items', ['group_id' => $group_id]);
        $this->db->delete('city_groups', ['id' => $group_id]);

        $this->db->trans_complete();

        if (!$this->db->trans_status()) {
            sendWebJsonResponse(true, 'Failed to delete city group');
            return;
        }

        sendWebJsonResponse(false, 'City Group deleted successfully');
    }
    public function get_city_groups()
    {
        $search = $this->input->get('q', true) ?? '';
        $limit = 20;
        $offset = 0;

        $groups = $this->Area_model->get_city_groups_list($search, $limit, $offset);

        // ✅ return flat array (NO "options")
        echo json_encode($groups['rows']);
    }



    public function get_zipcode_groups()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('admin/login', 'refresh');
        }

        $search = $this->input->get('q', true) ?? '';
        $limit = 20;
        $offset = 0;

        $data = $this->Area_model->get_zipcode_groups_list($search, $limit, $offset);

        // ✅ return plain array like cities & city groups
        echo json_encode($data);
    }




}
