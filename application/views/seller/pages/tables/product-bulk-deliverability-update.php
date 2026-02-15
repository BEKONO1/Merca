<div class="page-wrapper">

    <div class="page">

        <!-- BEGIN PAGE HEADER -->
        <div class="page-header d-print-none" aria-label="Page header">
            <div class="container-fluid">
                <div class="row g-2 align-items-center">

                    <!-- Page Title -->
                    <div class="col-12 col-md">
                        <h2 class="page-title mb-2 mb-md-0">Manage Products</h2>
                    </div>

                    <!-- Breadcrumb -->
                    <div class="col-12 col-md-auto">
                        <div class="d-flex justify-content-md-end">
                            <ol class="breadcrumb breadcrumb-arrows mb-0">
                                <li class="breadcrumb-item">
                                    <a href="<?= base_url('seller/home') ?>">Home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    Product Management
                                </li>
                            </ol>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- END PAGE HEADER -->

        <div class="page-body">
            <div class="container-fluid">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="ti ti-user-circle"></i> Manage Products</h3>
                            <div id="productToolbar">
                                <button type="button" class="btn btn-primary bg-primary-lt"
                                    id="openBulkDeliverabilityOffcanvas">
                                    <i class="ti ti-settings"></i> Bulk Deliverability Settings
                                </button>
                            </div>
                        </div>
                        <div class="card-body">

                            <table id="products_deliverability_table" class="table-striped" data-toggle="table"
                                data-url="<?= base_url('seller/product/get_deliverability_product_data_list') ?>"
                                data-side-pagination="server" data-pagination="true" data-search="true"
                                data-sort-name="id" data-sort-order="desc">
                                <thead>
                                    <tr>
                                        <th data-field="state" data-checkbox="true"></th>

                                        <th data-field="id" data-visible="false">ID</th>
                                        <th data-field="name" data-align="center">Product Name</th>
                                        <th data-field="brand" data-align="center">Brand</th>
                                        <th data-field="category" data-align="center">Category</th>

                                        <th data-field="deliverable_group_type" data-align="center">
                                            Zipcode Group Type
                                        </th>
                                        <th data-field="deliverable_zipcodes_group" data-align="center">
                                            Zipcodes Group
                                        </th>

                                        <th data-field="deliverable_city_group_type" data-align="center">
                                            City Group Type
                                        </th>
                                        <th data-field="deliverable_cities_group" data-align="center">
                                            Cities Group
                                        </th>

                                        <th data-field="operate" data-align="center">Action</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>

                        <div class="offcanvas offcanvas-end offcanvas-medium" tabindex="-1"
                            id="productDeliverabilitySetting" aria-labelledby="productDeliverabilitySettingLabel">
                            <div class="offcanvas-header">
                                <h2 class="offcanvas-title" id="productDeliverabilitySettingLabel">Product
                                    Deliverability Setting
                                </h2>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>
                            <form x-data="ajaxForm({
                                            url: base_url + 'seller/product/bulk_update_deliverability',
                                            offcanvasId: 'productDeliverabilitySetting',
                                            loaderText: 'Saving...'
                                        })" method="POST" class="form-horizontal" id="add_zipcode_form">
                                <div class="offcanvas-body">
                                    <div>

                                        <input type="hidden" name="product_id" id="modal_product_id">

                                        <!-- ZIPCODE GROUP TYPE -->
                                        <div class="mb-3">
                                            <label class="col-form-label">Zipcode Group Type</label>
                                            <select class="form-select" name="deliverable_group_type">
                                                <option value="0">None</option>
                                                <option value="1">All</option>
                                                <option value="3">Exclude</option>
                                            </select>
                                        </div>

                                        <!-- ZIPCODE GROUP -->
                                        <div class="mb-3">
                                            <label class="col-form-label">Zipcode Groups</label>

                                            <div x-data x-init="initTomSelect({
                                            element: $refs.zipGroupSelect,
                                            url: '/seller/area/get_zipcode_groups',
                                            placeholder: 'Select Zipcode Groups',
                                            maxItems: 20,
                                            plugins: ['remove_button']
                                        })">

                                                <select x-ref="zipGroupSelect" class="form-select"
                                                    name="deliverable_zipcodes_group[]" id="bulk_zipcode_group"
                                                    multiple>
                                                </select>
                                            </div>

                                            <small class="text-muted">
                                                Applied only when group type is Exclude
                                            </small>
                                        </div>

                                        <hr>

                                        <!-- CITY GROUP TYPE -->
                                        <div class="mb-3">
                                            <label class="col-form-label">City Group Type</label>
                                            <select class="form-select" name="deliverable_city_group_type">
                                                <option value="0">None</option>
                                                <option value="1">All</option>
                                                <option value="3">Exclude</option>
                                            </select>
                                        </div>

                                        <!-- CITY GROUP -->
                                        <div class="mb-3">
                                            <label class="col-form-label">City Groups</label>

                                            <div x-data x-init="initTomSelect({
                                            element: $refs.cityGroupSelect,
                                            url: '/seller/area/get_city_groups',
                                            placeholder: 'Select City Groups',
                                            maxItems: 20,
                                            plugins: ['remove_button']
                                        })">

                                                <select x-ref="cityGroupSelect" class="form-select"
                                                    name="deliverable_cities_group[]" id="bulk_city_group" multiple>
                                                </select>
                                            </div>

                                            <small class="text-muted">
                                                Applied only when group type is Exclude
                                            </small>
                                        </div>

                                    </div>
                                    <div class="text-end">
                                        <!-- <button type="reset" class="btn btn-warning ">Reset</button> -->
                                        <button type="button" class="btn" data-bs-dismiss="offcanvas"
                                            aria-label="Close">Close</button>
                                        <button type="submit" class="btn btn-primary Save" id="submit_btn">Save</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Bulk Affiliate Settings Offcanvas -->
                        <div class="offcanvas offcanvas-end offcanvas-medium" tabindex="-1"
                            id="bulkProductDeliverabilitySetting"
                            aria-labelledby="bulkProductDeliverabilitySettingLabel">

                            <div class="offcanvas-header">
                                <h2 class="offcanvas-title" id="bulkProductDeliverabilitySettingLabel">
                                    <i class="ti ti-settings"></i> Bulk Deliverability Settings
                                </h2>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>

                            <form x-data="ajaxForm({
                                url: base_url + 'seller/product/bulk_update_deliverability',
                                offcanvasId: 'bulkProductDeliverabilitySetting',
                                loaderText: 'Saving...'
                            })" method="POST" class="form-horizontal" id="bulkDeliverabilityForm">

                                <div class="offcanvas-body">

                                    <!-- Selected Products Info -->
                                    <div class="alert alert-info d-flex align-items-center">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <span id="selectedProductCount">0</span>&nbsp;product(s) selected
                                    </div>

                                    <input type="hidden" name="product_ids" id="product_ids">

                                    <!-- ZIPCODE GROUP TYPE -->
                                    <div class="mb-3">
                                        <label class="col-form-label">Zipcode Group Type</label>
                                        <select class="form-select" name="deliverable_group_type">
                                            <option value="0">None</option>
                                            <option value="1">All</option>
                                            <option value="3">Exclude</option>
                                        </select>
                                    </div>

                                    <!-- ZIPCODE GROUP -->
                                    <div class="mb-3">
                                        <label class="col-form-label">Zipcode Groups</label>

                                        <div x-data x-init="initTomSelect({
                                            element: $refs.zipGroupSelect,
                                            url: '/seller/area/get_zipcode_groups',
                                            placeholder: 'Select Zipcode Groups',
                                            maxItems: 20,
                                            plugins: ['remove_button']
                                        })">

                                            <select x-ref="zipGroupSelect" class="form-select"
                                                name="deliverable_zipcodes_group[]" id="bulk_zipcode_group" multiple>
                                            </select>
                                        </div>

                                        <small class="text-muted">
                                            Applied only when group type is Exclude
                                        </small>
                                    </div>

                                    <hr>

                                    <!-- CITY GROUP TYPE -->
                                    <div class="mb-3">
                                        <label class="col-form-label">City Group Type</label>
                                        <select class="form-select" name="deliverable_city_group_type">
                                            <option value="0">None</option>
                                            <option value="1">All</option>
                                            <option value="3">Exclude</option>
                                        </select>
                                    </div>

                                    <!-- CITY GROUP -->
                                    <div class="mb-3">
                                        <label class="col-form-label">City Groups</label>

                                        <div x-data x-init="initTomSelect({
                                            element: $refs.cityGroupSelect,
                                            url: '/seller/area/get_city_groups',
                                            placeholder: 'Select City Groups',
                                            maxItems: 20,
                                            plugins: ['remove_button']
                                        })">

                                            <select x-ref="cityGroupSelect" class="form-select"
                                                name="deliverable_cities_group[]" id="bulk_city_group" multiple>
                                            </select>
                                        </div>

                                        <small class="text-muted">
                                            Applied only when group type is Exclude
                                        </small>
                                    </div>

                                    <!-- ACTIONS -->
                                    <div class="text-end mt-4">
                                        <button type="button" class="btn" data-bs-dismiss="offcanvas">
                                            Cancel
                                        </button>

                                        <button type="submit" class="btn btn-primary" id="bulkSubmitBtn">
                                            Save
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>