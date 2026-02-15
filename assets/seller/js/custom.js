"use strict";

/* ---------------------------------------------------------------------------------------------------------------------------------------------------

Common-Functions or events
1.sales chart & product Chart 
2.consignmant settings
--------------------------------------------------------------------------------------------------------------------------------------------------- */


if (window.location.href.indexOf('seller/auth/sign_up') > -1) {

    document.addEventListener('DOMContentLoaded', function () {

        let currentStep = 1;
        const totalSteps = 4;

        const stepContents = document.querySelectorAll('.step-content');
        const stepItems = document.querySelectorAll('.step-item');
        const prevBtn = document.getElementById('prev_btn');
        const nextBtn = document.getElementById('next_btn');
        const submitBtn = document.getElementById('submit_btn');

        // ✅ Password toggle functionality
        document.querySelectorAll('.toggle-password').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-hp-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('ti-eye', 'ti-eye-off');
                } else {
                    input.type = 'password';
                    icon.classList.replace('ti-eye-off', 'ti-eye');
                }
            });
        });

        // ✅ Show specific step
        function showStep(step) {
            stepContents.forEach(content => content.style.display = 'none');
            document.getElementById('step-' + step).style.display = 'block';

            stepItems.forEach((item, index) => {
                item.classList.toggle('active', index + 1 === step);
            });

            prevBtn.style.display = (step === 1) ? 'none' : 'inline-block';
            nextBtn.style.display = (step === totalSteps) ? 'none' : 'inline-block';
            submitBtn.style.display = (step === totalSteps) ? 'inline-block' : 'none';

            currentStep = step;
        }

        // ✅ Validate inputs of the current step
        function validateStep(step) {
            const stepElement = document.getElementById('step-' + step);
            const inputs = stepElement.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            let errorMessage = '';

            inputs.forEach(input => {
                // Required blank validation
                if (!input.value.trim()) {
                    valid = false;
                    if (!errorMessage) {
                        const label = input.closest('.mb-3, .col-md-6, .col-12')?.querySelector('label')?.textContent || 'This field';
                        errorMessage = `${label.replace('*', '').trim()} is required`;
                    }
                }

                // TomSelect multiple select validation
                if (input.tagName.toLowerCase() === 'select' && input.tomselect) {
                    const tsValue = input.tomselect.getValue();
                    if (!tsValue || tsValue.length === 0) {
                        valid = false;
                        if (!errorMessage) {
                            const label = input.closest('.mb-3, .col-md-6, .col-12')?.querySelector('label')?.textContent || 'This field';
                            errorMessage = `${label.replace('*', '').trim()} is required`;
                        }
                    }
                }

                // Email format validation
                if (input.type === 'email' && input.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        valid = false;
                        if (!errorMessage) {
                            errorMessage = `Email is not a valid email address`;
                        }
                    }
                }
                if (input.id === 'password' && input.value.trim()) {
                    const strongPassRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                    if (!strongPassRegex.test(input.value.trim())) {
                        valid = false;
                        if (!errorMessage) {
                            errorMessage =
                                'Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.';
                        }
                    }
                }

            });

            // Password match validation (Step 1 only)
            if (step === 1 && valid) {
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm_password');
                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    valid = false;
                    errorMessage = 'Passwords don’t match. Please re-enter.';
                }
            }

            if (!valid && errorMessage) {
                // alert(errorMessage); // Replace with your toast or inline message if needed
                showToast(errorMessage, 'error')
            }

            return valid;
        }

        // ✅ Navigation Buttons
        nextBtn.addEventListener('click', function () {
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
            }
        });

        prevBtn.addEventListener('click', function () {
            showStep(currentStep - 1);
        });

        // ✅ Step item click (manual navigation)
        stepItems.forEach((item, index) => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                const targetStep = index + 1;

                if (targetStep < currentStep || (targetStep === currentStep + 1 && validateStep(currentStep))) {
                    showStep(targetStep);
                }
            });
        });

        // ✅ Initialize first step
        showStep(1);

        // ✅ Toggle Zipcode field
        const zipcodeTypeSelect = document.getElementById('deliverable_zipcode_type');
        const serviceableZipcodesSelect = document.getElementById('deliverable_zipcodes');

        if (zipcodeTypeSelect && serviceableZipcodesSelect) {
            function toggleServiceableZipcodes() {
                const selectedValue = zipcodeTypeSelect.value;
                serviceableZipcodesSelect.disabled = (selectedValue === '<?= ALL ?>');
                serviceableZipcodesSelect.closest('.col-md-6').style.opacity =
                    serviceableZipcodesSelect.disabled ? '0.5' : '1';
            }
            toggleServiceableZipcodes();
            zipcodeTypeSelect.addEventListener('change', toggleServiceableZipcodes);
        }

        // ✅ Toggle City field
        const cityTypeSelect = document.getElementById('deliverable_city_type');
        const serviceableCitiesSelect = document.getElementById('deliverable_cities');

        if (cityTypeSelect && serviceableCitiesSelect) {
            function toggleServiceableCities() {
                const selectedValue = cityTypeSelect.value;
                serviceableCitiesSelect.disabled = (selectedValue === '<?= ALL ?>');
                serviceableCitiesSelect.closest('.col-md-6').style.opacity =
                    serviceableCitiesSelect.disabled ? '0.5' : '1';
            }
            toggleServiceableCities();
            cityTypeSelect.addEventListener('change', toggleServiceableCities);
        }

    });
}


// if (window.location.href.indexOf('seller/auth/sign_up') > -1) {

//     document.addEventListener('DOMContentLoaded', function () {

//         let currentStep = 1;
//         const totalSteps = 4;

//         const stepContents = document.querySelectorAll('.step-content');
//         const stepItems = document.querySelectorAll('.step-item');
//         const prevBtn = document.getElementById('prev_btn');
//         const nextBtn = document.getElementById('next_btn');
//         const submitBtn = document.getElementById('submit_btn');

//         // Password toggle functionality
//         document.querySelectorAll('.toggle-password').forEach(function (toggle) {
//             toggle.addEventListener('click', function (e) {
//                 e.preventDefault();
//                 const targetId = this.getAttribute('data-hp-target');
//                 const input = document.getElementById(targetId);
//                 const icon = this.querySelector('i');

//                 if (input.type === 'password') {
//                     input.type = 'text';
//                     icon.classList.replace('ti-eye', 'ti-eye-off');
//                 } else {
//                     input.type = 'password';
//                     icon.classList.replace('ti-eye-off', 'ti-eye');
//                 }
//             });
//         });

//         function showStep(step) {
//             stepContents.forEach(content => content.style.display = 'none');
//             document.getElementById('step-' + step).style.display = 'block';

//             stepItems.forEach((item, index) => {
//                 item.classList.toggle('active', index + 1 === step);
//             });

//             prevBtn.style.display = (step === 1) ? 'none' : 'inline-block';
//             nextBtn.style.display = (step === totalSteps) ? 'none' : 'inline-block';
//             submitBtn.style.display = (step === totalSteps) ? 'inline-block' : 'none';

//             currentStep = step;
//         }

//         function validateStep(step) {
//             const stepElement = document.getElementById('step-' + step);
//             const inputs = stepElement.querySelectorAll('input[required], select[required], textarea[required]');
//             let valid = true;
//             let errorMessage = '';

//             inputs.forEach(input => {

//                 console.log(input.type);

//                 // Required blank validation
//                 if (!input.value.trim()) {
//                     valid = false;
//                     if (!errorMessage) {
//                         const label = input.closest('.mb-3, .col-md-6, .col-12')?.querySelector('label')?.textContent || 'This field';
//                         errorMessage = `${label.replace('*', '').trim()} is required`;
//                     }
//                 }

//                 // TomSelect multiple select validation
//                 if (input.tagName.toLowerCase() === 'select' && input.tomselect) {
//                     const tsValue = input.tomselect.getValue();
//                     if (!tsValue || tsValue.length === 0) {
//                         valid = false;
//                         if (!errorMessage) {
//                             const label = input.closest('.mb-3, .col-md-6, .col-12')?.querySelector('label')?.textContent || 'This field';
//                             errorMessage = `${label.replace('*', '').trim()} is required`;
//                         }
//                     }
//                 }

//                 // ✅ Email format validation
//                 if (input.type == 'email' && input.value.trim()) {
//                     const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//                     if (!emailRegex.test(input.value.trim())) {
//                         valid = false;
//                         if (!errorMessage) {
//                             errorMessage = `Email is not a valid email address`;
//                         }
//                     }
//                 }


//                 // ✅ Password match validation (Step 1 only)
//                 if (step === 1 && valid) {
//                     const password = document.getElementById('password');
//                     const confirmPassword = document.getElementById('confirm_password');

//                     if (password && confirmPassword && password.value !== confirmPassword.value) {
//                         valid = false;
//                         errorMessage = 'Passwords don’t match. Please re-enter.';
//                     }
//                 }


//                 if (input.type === 'email' && input.value.trim()) {
//                     const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//                     if (!emailRegex.test(input.value.trim())) {
//                         valid = false;
//                         if (!errorMessage) {
//                             const label = input.closest('.mb-3, .col-md-6, .col-12')?.querySelector('label')?.textContent || 'Email';
//                             errorMessage = `${label.replace('*', '').trim()} is not a valid email address`;
//                         }
//                     }

//                     return valid;
//                 }

//             });

//             nextBtn.addEventListener('click', function () {
//                 if (validateStep(currentStep)) {
//                     showStep(currentStep + 1);
//                 }
//             });

//             prevBtn.addEventListener('click', function () {
//                 showStep(currentStep - 1);
//             });

//             stepItems.forEach((item, index) => {
//                 item.addEventListener('click', function (e) {
//                     e.preventDefault();
//                     const targetStep = index + 1;

//                     if (targetStep < currentStep || (targetStep === currentStep + 1 && validateStep(currentStep))) {
//                         showStep(targetStep);
//                     }
//                 });
//             });

//             showStep(1);

//             // Toggle Zipcode field
//             const zipcodeTypeSelect = document.getElementById('deliverable_zipcode_type');
//             const serviceableZipcodesSelect = document.getElementById('deliverable_zipcodes');

//             if (zipcodeTypeSelect && serviceableZipcodesSelect) {
//                 function toggleServiceableZipcodes() {
//                     const selectedValue = zipcodeTypeSelect.value;
//                     serviceableZipcodesSelect.disabled = (selectedValue === '<?= ALL ?>');
//                     serviceableZipcodesSelect.closest('.col-md-6').style.opacity = serviceableZipcodesSelect.disabled ? '0.5' : '1';
//                 }
//                 toggleServiceableZipcodes();
//                 zipcodeTypeSelect.addEventListener('change', toggleServiceableZipcodes);
//             }

//             // Toggle City field
//             const cityTypeSelect = document.getElementById('deliverable_city_type');
//             const serviceableCitiesSelect = document.getElementById('deliverable_cities');

//             if (cityTypeSelect && serviceableCitiesSelect) {
//                 function toggleServiceableCities() {
//                     const selectedValue = cityTypeSelect.value;
//                     serviceableCitiesSelect.disabled = (selectedValue === '<?= ALL ?>');
//                     serviceableCitiesSelect.closest('.col-md-6').style.opacity = serviceableCitiesSelect.disabled ? '0.5' : '1';
//                 }
//                 toggleServiceableCities();
//                 cityTypeSelect.addEventListener('change', toggleServiceableCities);
//             }

//         });
// }

//login handeller 
$(document).ready(function () {
    console.log("Login JS loaded successfully");

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        console.log("Form submitted");

        let $btn = $('#loginBtn');
        $btn.prop('disabled', true).text('Please wait...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                console.log("AJAX success:", response);

                // Update CSRF token if provided
                if (response.csrfName && response.csrfHash) {
                    $('input[name="' + response.csrfName + '"]').val(response.csrfHash);
                }

                if (response.error === false) {

                    alert(response.message);


                    setTimeout(function () {
                        window.location.href = base_url + 'seller/home';
                    }, 1000);
                } else {

                    alert(response.message);
                }

                $btn.prop('disabled', false).text('Sign In');
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", error);
                $btn.prop('disabled', false).text('Sign In');
                location.reload(); // Refresh the page
            }
        });
    });
});
$(document).ready(function () {
    // Handle seller registration form submission via AJAX
    $(document).on("submit", "#add_seller_form", function (e) {
        e.preventDefault();
        console.log("Form submit triggered");

        // ====== 🔹 Validate Required Fields ======
        let isValid = true;
        $("#add_seller_form [required]").each(function () {
            if ($.trim($(this).val()) === "") {
                isValid = false;
                // Optional: highlight the empty field
                $(this).addClass("is-invalid");
            } else {
                $(this).removeClass("is-invalid");
            }
        });

        if (!isValid) {
            showToast("Please fill all required fields.", "error");
            return; // stop further execution
        }

        const $btn = $("#submit_btn");
        const btnText = $btn.html();
        $btn.prop("disabled", true).html('<i class="ti ti-loader ti-spin me-2"></i> Please wait...');

        const formData = new FormData(this);

        try {
            formData.append(csrfName, csrfHash);
        } catch (e) {
            console.error("CSRF Error:", e);
            showToast("CSRF variables not found. Check if csrfName and csrfHash are defined.", "error");
            $btn.prop("disabled", false).html(btnText);
            return;
        }

        $.ajax({
            url: base_url + "seller/auth/create_seller",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response) {
                console.log("Response received:", response);

                if (!response || typeof response !== "object") {
                    console.error("Invalid Response:", response);
                    showToast("Invalid response format (not JSON). Check console for details.", "error");
                    $btn.prop("disabled", false).html(btnText);
                    return;
                }

                if (response.error === false) {
                    console.log("Registration successful:", response.message);
                    showToast(response.message || "Registration successful!", "success");
                    setTimeout(() => {
                        window.location.href = base_url + "seller/login";
                    }, 1500);
                } else {
                    console.warn("Registration failed:", response.message);
                    showToast(response.message || "Something went wrong. Please check your form.", "error");
                }

                // Update CSRF if provided
                if (response.csrfName && response.csrfHash) {
                    csrfName = response.csrfName;
                    csrfHash = response.csrfHash;
                }

                $btn.prop("disabled", false).html(btnText);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                showToast("Something went wrong. Please try again.", "error");
                $btn.prop("disabled", false).html(btnText);
            }
        });
    });
});



// =====================================
// sales charts
// =====================================
if (
    (window.location.href.indexOf('seller/home') > -1)
) {
    document.addEventListener('DOMContentLoaded', function () {
        function fetchAndRenderCharts() {
            axios
                .get(base_url + from + '/home/fetch_sales', {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest', // Optional, mimics jQuery's default
                    },
                })
                .then(function (response) {
                    const { data } = response;
                    let monthlyData = data[0];
                    let weeklyData = data[1];
                    let dailyData = data[2];

                    const dataObj = {
                        Monthly: {
                            series: [
                                {
                                    name: 'Monthly Revenue',
                                    data: monthlyData.total_sale || [],
                                },
                            ],
                            categories: monthlyData.month_name || [],
                            color: '#2e81e6ff', // Vibrant blue, works in both themes
                        },
                        Weekly: {
                            series: [
                                {
                                    name: 'Weekly Revenue',
                                    data: weeklyData.total_sale || [],
                                },
                            ],
                            categories: weeklyData.week || [],
                            color: '#2ef180ff', // Green, works in both themes
                        },
                        Daily: {
                            series: [
                                {
                                    name: 'Daily Revenue',
                                    data: dailyData.total_sale || [],
                                },
                            ],
                            categories: dailyData.day || [],
                            color: '#e73b28ff', // Red, works in both themes
                        },
                    };

                    let chartData = dataObj['Monthly'];

                    const options = {
                        chart: {
                            type: 'line',
                            height: 350,
                            fontFamily: 'inherit',
                            toolbar: { show: false },
                            animations: { enabled: true },
                        },
                        series: chartData.series,
                        colors: [chartData.color],
                        dataLabels: {
                            enabled: false,
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3,
                        },
                        xaxis: {
                            categories: chartData.categories,
                            labels: {
                                style: {
                                    colors: 'var(--tblr-body-color)',
                                },
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: 'var(--tblr-body-color)',
                                },
                                formatter: function (value) {
                                    return (value / 1000) + '00k';
                                },
                            },
                        },
                        grid: {
                            borderColor: 'var(--tblr-border-color)',
                        },
                        fill: {
                            type: 'solid',
                            opacity: 0.2,
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    var currencySymbol = '<?php echo $currency_symbol; ?>';
                                    return currencySymbol + val;
                                },
                            },
                            theme: document.documentElement.getAttribute('data-bs-theme') || 'light',
                        },
                        markers: {
                            size: 4,
                            hover: {
                                size: 6,
                            },
                        },
                    };

                    const chart = new ApexCharts(document.querySelector('#Chart'), options);
                    chart.render();

                    // Update chart on tab click
                    const tabLinks = document.querySelectorAll('.sales-tab li a');
                    tabLinks.forEach((link) => {
                        link.addEventListener('click', function (e) {
                            e.preventDefault();
                            tabLinks.forEach((l) => l.classList.remove('active'));
                            this.classList.add('active');
                            chartData = dataObj[this.getAttribute('href').replace('#', '')];
                            chart.updateOptions({
                                series: chartData.series,
                                colors: [chartData.color],
                                xaxis: {
                                    categories: chartData.categories,
                                },
                            });
                        });
                    });
                })
                .catch(function (error) {
                    console.error('Error fetching data: ', error);
                });
        }

        fetchAndRenderCharts();
    });
}
// product chart
document.addEventListener('DOMContentLoaded', function () {
    const piechartEl = document.getElementById('piechart_3d');
    if (!piechartEl) return;

    const formData = new FormData();

    axios.post(base_url + 'seller/sales_inventory/top_selling_products', formData, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function (response) {
            const result = response.data;

            if (!result || result.length <= 1) {
                piechartEl.innerHTML = '<p class="text-center text-muted">No stock data available</p>';
                return;
            }

            const labels = [];
            const series = [];

            for (let i = 1; i < result.length; i++) {
                let label = result[i][0];
                if (label.length > 20) label = label.substring(0, 17) + '...';
                labels.push(label);
                series.push(parseInt(result[i][1]));
            }

            const chart = new ApexCharts(piechartEl, {
                chart: {
                    type: 'donut',
                    height: "100%",
                    width: "100%",
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: true },
                },

                series: series,
                labels: labels,

                legend: {
                    show: true,
                    position: 'right',
                    fontSize: '13px'
                },

                // ✅ Removed percentage from donut center
                dataLabels: {
                    enabled: false
                },

                // ✅ Show only TOTAL in the center
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '14px' },
                                value: {
                                    show: true,
                                    formatter: (val) => Math.round(val) + ' Units'
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: w => `${w.globals.series.reduce((a, b) => a + b, 0)} Units`
                                }
                            }
                        }
                    }
                },

                tooltip: {
                    enabled: true,
                    y: {
                        formatter: function (val) {
                            return val + " Units"; // ✅ show only units on hover, not percentage
                        }
                    }
                },

                responsive: [
                    {
                        breakpoint: 1200,
                        options: { legend: { position: "bottom" } }
                    },
                    {
                        breakpoint: 768,
                        options: { chart: { height: 300 }, legend: { position: "bottom", fontSize: "11px" } }
                    },
                    {
                        breakpoint: 480,
                        options: { chart: { height: 260 } }
                    }
                ]
            });

            chart.render();

            window.addEventListener("resize", function () {
                chart.updateOptions({});
            });

        })
        .catch(function (error) {
            piechartEl.innerHTML = '<p class="text-danger text-center">Error loading chart</p>';
            console.error(error);
        });
});





//vew consignment
document.addEventListener('click', function (event) {
    let target = event.target.closest('.view_consignment_items');
    if (!target) return;

    // Parse consignment items data
    let consignment_items = JSON.parse(target.getAttribute('data-items'));

    // Get modal body container
    let modalBody = document.getElementById('consignment_details');
    modalBody.innerHTML = '';

    if (!consignment_items || consignment_items.length === 0) {
        modalBody.innerHTML = `<div class="text-center text-muted py-4">
            <i class="ti ti-package ti-2x mb-2"></i><br>
            No items found in this consignment.
        </div>`;
        return;
    }

    // Create table for items
    let table = document.createElement('table');
    table.className = 'table table-vcenter table-striped card-table align-middle';

    let thead = document.createElement('thead');
    thead.innerHTML = `
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Image</th>
            <th>Quantity</th>
        </tr>`;
    table.appendChild(thead);

    let tbody = document.createElement('tbody');
    consignment_items.forEach((item, index) => {
        let row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>
                <div class="fw-bold">${item.product_name}</div>
                ${item.sku ? `<div class="text-muted small">SKU: ${item.sku}</div>` : ''}
            </td>
            <td>
                <a href="${item.image}" data-toggle="lightbox" data-gallery="order-images" 
                   class="d-inline-block">
                    <img src="${item.image}" alt="${item.product_name}" 
                         class="rounded border" style="width:60px; height:60px; object-fit:cover;">
                </a>
            </td>
            <td><span class="badge bg-primary-lt">${item.quantity}</span></td>
        `;
        tbody.appendChild(row);
    });

    table.appendChild(tbody);
    modalBody.appendChild(table);

    // Show offcanvas
    let offcanvasEl = document.getElementById('view_consignment_items_modal');
    let bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
    bsOffcanvas.show();
});

function consignmentModal(seller_id = null) {

    let shiprocket_order = $("#is_shiprocket_order_check").val() == "1";

    let productVariantIds = []
    let productName = []
    let orderItemId = []
    let orderPickupLocation = []
    let orderPickupLocationData = [] // Store both ID and name
    let orderItemIds = []

    $('.product_variant_id').each(function () {
        productVariantIds.push($(this).val());
    });

    productVariantIds.map(function (value) {
        let itemData = JSON.parse($("#product_variant_id_" + value).text());
        orderItemIds.push(itemData["id"])
        productName.push(itemData["product_name"] || "Product"); // Get product name from JSON data
        orderPickupLocation.push(itemData["pickup_location"]);

        // Store pickup location with its name
        if (itemData["pickup_location"]) {
            orderPickupLocationData.push({
                id: itemData["pickup_location"],
                name: itemData["pickup_location_name"] || itemData["pickup_location"]
            });
        }
    });

    // Create unique set of pickup locations based on ID
    let pickupLocationMap = {};
    orderPickupLocationData.forEach(function (loc) {
        if (!pickupLocationMap[loc.id]) {
            pickupLocationMap[loc.id] = loc.name;
        }
    });

    // Create options array with ID as value and name as text
    let options = Object.keys(pickupLocationMap).map(function (id) {
        let locationName = pickupLocationMap[id];
        // Fallback to pickupLocationsMap if name is empty or same as ID
        if (!locationName || locationName === id) {
            locationName = (typeof pickupLocationsMap !== 'undefined' && pickupLocationsMap[id]) ? pickupLocationsMap[id] : id;
        }
        return {
            value: id, text: locationName
        }
    });

    $("#parcel_pickup_locations").empty(); // Clear existing options
    $("#parcel_pickup_locations").append(new Option("Select Option", "")); // Add default option
    options.forEach(option => {
        $("#parcel_pickup_locations").append(new Option(option.text, option.value));
    });

    var modalBody = document.getElementById('product_details');
    if (modalBody == null) {
        showToast(
            "Order status is still awaiting. You cannot create a parcel."
        );
    }

    modalBody.innerHTML = '';

    for (var i = 0; i < productVariantIds.length; i++) {
        const data = JSON.parse($("#product_variant_id_" + productVariantIds[i]).html());

        const quantity = parseInt(data.quantity);
        const unit_price = parseInt(data.unit_price);
        const delivered_quantity = parseInt(data.delivered_quantity);
        const pickupLocationId = data.pickup_location || '';

        if (delivered_quantity != quantity && data.active_status != "cancelled" && data.active_status != "delivered") {
            $('#empty_box_body').addClass("d-none");
            $('#modal-body').removeClass("d-none");
            let row = "<tr id='parcel_row_" + productVariantIds[i] + "' data-pickup='" + pickupLocationId + "' >" +
                "<th scope='row'>" + orderItemIds[i] + "</th>" +
                "<td>" + productName[i] + "</td>" +
                "<td>" + productVariantIds[i] + "</td>" +
                "<td>" + quantity + "</td>" +
                "<td>" + unit_price + "</td>" +
                "<td><input type='checkbox' data-item-id='" + orderItemIds[i] + "' name='checkbox-" + productVariantIds[i] + "' id='checkbox-" + productVariantIds[i] + "' class='product-to-ship'></td>" +
                "</tr>";

            modalBody.innerHTML += row;
        }
    }
    if (modalBody.innerHTML == "") {
        $('#empty_box_body').removeClass("d-none");
        $('#modal-body').addClass("d-none");

        let empty_box_body = document.getElementById('empty_box_body');
        empty_box_body.innerHTML = "";
        let row = "<h5 class='text-center'>Items Are Already Shipped.</h5>";
        empty_box_body.innerHTML += row;
    }


    // Add event listener for dropdown change
    $("#parcel_pickup_locations").on("change", function () {
        const selectedPickupLocation = $(this).val();
        console.log('Selected pickup location:', selectedPickupLocation, 'Shiprocket order:', shiprocket_order);

        // Uncheck all checkboxes
        $(".product-to-ship").prop("checked", false);

        // For non-shiprocket orders, show all rows
        if (!shiprocket_order) {
            $("tr[id^='parcel_row_']").show();
            return;
        }

        // For shiprocket orders, filter by pickup location
        if (selectedPickupLocation === "") {
            // Hide all rows if no option is selected
            $("tr[id^='parcel_row_']").hide();
        } else {
            // Show rows that match the selected location and hide the others
            $("tr[id^='parcel_row_']").each(function () {
                const rowPickupLocation = String($(this).data("pickup"));
                console.log('Comparing - Row:', rowPickupLocation, 'Selected:', selectedPickupLocation, 'Match:', rowPickupLocation == selectedPickupLocation);

                // Convert both to strings for comparison to avoid type mismatch
                if (rowPickupLocation == selectedPickupLocation) {
                    $(this).show(); // Show rows that match
                } else {
                    $(this).hide(); // Hide rows that don't match
                }
            });
        }
    });

    // Trigger change event only if dropdown exists, otherwise show all rows for non-shiprocket orders
    if ($("#parcel_pickup_locations").length > 0) {
        $("#parcel_pickup_locations").change();
    } else if (!shiprocket_order) {
        // For non-shiprocket orders without pickup dropdown, show all rows immediately
        $("tr[id^='parcel_row_']").show();
    }

}

document.addEventListener('DOMContentLoaded', function () {
    const createConsignmentOffcanvas = document.getElementById('create_consignment_offcanvas');
    if (createConsignmentOffcanvas) {
        createConsignmentOffcanvas.addEventListener('shown.bs.offcanvas', function () {
            consignmentModal();
        });
    }
});


// Handle offcanvas show

$(document).on('click', '#ship_parcel_btn', function (e) {
    e.preventDefault();

    let product_to_ship = $('.product-to-ship:checked');
    let consignment_title = $('#consignment_title').val();
    let order_id = $('#order_id').val();

    let selected_items = [];
    product_to_ship.each(function () {
        selected_items.push($(this).data("item-id"));
    });

    // Notiflix confirm before request
    Notiflix.Confirm.show(
        'Confirm Shipment',
        'Are you sure you want to create this consignment?',
        'Yes, Ship',
        'Cancel',
        function okCb() {

            $.ajax({
                type: "POST",
                url: base_url + from + "/orders/create_consignment",
                data: {
                    consignment_title,
                    selected_items,
                    order_id,
                    [csrfName]: csrfHash,
                },
                success: function (response) {
                    response = JSON.parse(response);

                    csrfName = response['csrfName'];
                    csrfHash = response['csrfHash'];

                    if (!response.error) {

                        response.data.map(val => {
                            $("#product_variant_id_" + val.product_variant_id).html(JSON.stringify(val))
                        });

                        $("#consignment_table").bootstrapTable('refresh');

                        $('.offcanvas-backdrop').removeClass("show");
                        const offcanvasEl = document.getElementById('create_consignment_offcanvas');
                        if (offcanvasEl) {
                            offcanvasEl.classList.remove("show");
                            offcanvasEl.style.visibility = "hidden";
                            offcanvasEl.style.opacity = "0";
                            offcanvasEl.style.transform = "translateX(100%)"; // slide out animation
                        }

                        showToast(response.message, 'success');
                    } else {
                        showToast(response.message, 'error');
                    }
                }
            });
        },
        function cancelCb() {
            showToast('Consignment creation cancelled.', 'info');
        }
    );
});

// =====================================
// Product faQs Edit and Add
// =====================================

$(document).ready(function () {


    $('.addProductFaq').on('click', function () {
        $('.offcanvas-title').text('Add FAQs');
        $('#submit_btn').html('<i class="ti ti-device-floppy me-2"></i> Add FAQs');


        $('#product_select_wrapper').removeClass('d-none');


        $('#edit_product_faq').val('');
        $('#hidden_question').val('');
        $('#question').val('').prop('disabled', false);
        $('#answer').val('');
        $('#product_id').val('');


        if (window.TomSelect && $('#product_id')[0] && $('#product_id')[0].tomselect) {
            $('#product_id')[0].tomselect.clear();
        }


        $('#question').focus();
    });



    // Edit FAQ button
    $('#products_faqs_table').on('click-cell.bs.table', function (event, field, value, row, $el) {


        $('.offcanvas-title').text('Update FAQs');
        $('#submit_btn').html('<i class="ti ti-device-floppy me-2"></i> Update FAQs');

        $('#product_select_wrapper').addClass('d-none');

        $('#edit_product_faq').val(row.id);

        $('#question').val(row.question).prop('disabled', false);
        $('#hidden_question').val(row.question);
        $('#answer').val(row.answer);
        $('#hidden_product_id').val(row.product_id);

        $('#product_id').val('');

        if (window.TomSelect && $('#product_id')[0]?.tomselect) {
            $('#product_id')[0].tomselect.clear();
        }

        $('#answer').focus();
    });



});


function faqParams(p) {
    return {
        "user_id": $('#user_id').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}



// =====================================
// Product Bulk Affiliate Settings
// =====================================
// affiliate Settings

$(document).on('click', '.open-affiliate-modal', function () {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const isInAffiliate = $(this).data('is_in_affiliate');

    $('#modal_product_id').val(id);
    $('#modal_product_name').val(name);
    $('#modal_is_in_affiliate').val(isInAffiliate);
});

$(document).on('click', '.affiliateFormSave', function (e) {
    e.preventDefault();

    var product_id = $('#modal_product_id').val();
    var product_name = $('#modal_product_name').val();
    var is_in_affiliate = $('#modal_is_in_affiliate').val();

    $.ajax({
        url: base_url + from + '/product/update_affiliate_settings',
        method: 'POST',
        data: {
            product_id: product_id,
            product_name: product_name,
            is_in_affiliate: is_in_affiliate
        },
        success: function (response) {

            response = JSON.parse(response);

            showToast("updated successfully!", "success");

            $('#product-affiliate-modal').modal('hide');
            $('table').bootstrapTable('refresh');
            // Optionally reload table or update UI
        },
        error: function () {
            showToast('Failed to update!', "error");
        }
    });
});

$(document).ready(function () {


    $('#openBulkModal').on('click', function () {
        var selectedProducts = $('#products_affiliate_table').bootstrapTable('getSelections');

        if (selectedProducts.length === 0) {
            showToast('Please select at least one product to update.', 'error');
            return;
        }


        var product_ids = $.map(selectedProducts, function (row) {
            return row.id;
        });



        $('#product_ids').val(product_ids);


        $('#selectedProductCount').text(selectedProducts.length);


        $('#bulkAffiliateModal').offcanvas('show');
    });


    $('#bulkAffiliateForm').on('submit', function (e) {
        e.preventDefault();

        var product_ids = $('#bulk_affiliate_product_ids').val();
        var is_in_affiliate = $('#bulk_affiliate_status').val();


        let csrfName = $("input[name='csrf_test_name']").attr("name");
        let csrfHash = $("input[name='csrf_test_name']").val();

        $.ajax({
            url: base_url + from + '/product/bulk_update_affiliate',
            method: 'POST',
            data: {
                product_ids: product_ids,
                is_in_affiliate: is_in_affiliate,
                [csrfName]: csrfHash
            },
            success: function (response) {

                response = JSON.parse(response);


                if (response.csrfHash) {
                    $("input[name='csrf_test_name']").val(response.csrfHash);
                }

                if (!response.error) {
                    showToast(response.message, 'success');
                    $('#bulkAffiliateModal').offcanvas('hide');
                    $('#products_affiliate_table').bootstrapTable('refresh');
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function () {
                showToast('Update failed. Please try again.', 'error');
            }
        });
    });

});

function brand_query_params(p) {
    return {
        "brand_id": $('#brand_id').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function seller_wallet_query_params(p) {
    return {
        "transaction_status_type_filter": $('#transaction_status_type_filter').val(),
        "payment_type": $('#payment_type').val(),
        transaction_type: 'wallet',
        user_type: 'seller',
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function sales_report_query_params(p) {
    return {
        "start_date": $('#start_date').val(),
        "end_date": $('#end_date').val(),
        "seller_id": $('#seller_filter').val(),
        "payment_method": $('#payment_method_filter').val(),
        "order_status": $('#order_status_filter').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function sales_inventory_report_query_params(p) {
    return {
        "start_date": $('#start_date').val(),
        "end_date": $('#end_date').val(),
        "seller_id": $('#seller_ids').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

// =====================================
// bulk update
// =====================================

$('#bulk_upload_form').on('submit', function (e) {
    e.preventDefault();

    var type = $('#type').val();
    if (!type) {
        showToast("Please select type.", "warn");
        return;
    }

    var formdata = new FormData(this);

    // Get fresh CSRF from hidden input or global var
    var csrfName = $('input[name="' + '<?= $this->security->get_csrf_token_name(); ?>').attr('name');
    var csrfHash = $('input[name="' + '<?= $this->security->get_csrf_token_name(); ?>').val();

    formdata.append(csrfName, csrfHash);

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formdata,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function () {
            $('#submit_btn').html('Please Wait...').prop('disabled', true);
        },
        success: function (result) {
            // Update CSRF for next request
            $('input[name="' + result.csrfName + '"]').val(result.csrfHash);

            if (result.error === false) {
                showToast(result.message, "success");
            } else {
                showToast(Array.isArray(result.message) ? result.message.join('<br>') : result.message, "error");
            }
        },
        error: function () {
            showToast("Something went wrong. Please try again.", "error");
        },
        complete: function () {
            $('#submit_btn').html('Submit').prop('disabled', false);
        }
    });
});
// =====================================
// manage stock 
// =====================================
$(document).on('click', '.edit_stock_btn', function (e) {
    e.preventDefault();
    var variant_id = $(this).data('id');

    // Fetch variant data via AJAX
    $.ajax({
        url: base_url + 'seller/manage_stock/get_variant_data',
        type: 'GET',
        data: { edit_id: variant_id },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('#product_name').val(response.data.product_name);
                $('#current_stock').val(response.data.current_stock);
                $('#quantity').val('');
                $('#type').val('add');
                $('input[name="variant_id"]').val(response.data.variant_id);
            }
        },

    });
});

var auth_settings = $('#auth_settings').val();
$(function () {
    $('[data-toggle="popover"]').popover()
})
$(document).ready(function () {
    $('#loading').hide();
});

var from = 'admin';
if (window.location.href.indexOf("seller/") > -1) {
    from = 'seller';
}

$.event.special.touchstart = {
    setup: function (_, ns, handle) {
        this.addEventListener("touchstart", handle, {
            passive: !ns.includes("noPreventDefault")
        });
    }
};




$('.table-striped').bootstrapTable({
    iconsPrefix: 'ti',
    icons: {
        refresh: 'ti ti-refresh',
        toggleOff: 'ti ti-toggle-off',
        toggleOn: 'ti ti-toggle-on',
        columns: 'ti ti-columns',
        detailOpen: 'ti ti-plus',
        detailClose: 'ti ti-minus',
        fullscreen: 'ti ti-expand',
        search: 'ti ti-search',
        clearSearch: 'ti ti-trash'
    }
});


$(function () {
    $('[data-toggle="tooltip"]').tooltip()
});

$(document).on("click", '[data-toggle="lightbox"]', function (event) {
    event.preventDefault();
    $(this).ekkoLightbox();
});

var url = window.location.origin + window.location.pathname;
var $selector = $('.sidebar a[href="' + url + '"]');
$($selector).addClass('active');
$($selector).closest('ul').closest('li').addClass('menu-open');
$($selector).closest('ul').removeAttr('style');
$($selector).closest('ul').closest('li').find('a[href*="#"').addClass('active');

var tmp = [];
var permute_counter = 0;

//User defined functions

function containsAll(needles, haystack) {
    for (var i = 0; i < needles.length; i++) {
        if ($.inArray(needles[i], haystack) == -1) return false;
    }
    return true;
}

function getPermutation(args) {
    var r = [],
        max = args.length - 1;

    function helper(arr, i) {
        for (var j = 0, l = args[i].length; j < l; j++) {
            var a = arr.slice(0); // clone arr
            a.push(args[i][j]);
            if (i == max)
                r.push(a);
            else
                helper(a, i + 1);
        }
    }
    helper([], 0);
    return r;
}


function clear_form_elements(class_name) {
    jQuery("." + class_name).find(':input').each(function () {
        switch (this.type) {
            case 'password':
            case 'text':
            case 'textarea':
            case 'file':
            case 'select-one':
            case 'select-multiple':
            case 'date':
            case 'number':
            case 'tel':
            case 'email':
                jQuery(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
                break;
        }
    });
}
function updateSalesInventoryChart() {
    if (document.getElementById('sales_piechart_3d')) {
        loadSalesInventoryChart();
    }
}

function status_date_wise_search() {
    $('.table-striped').bootstrapTable('refresh');
    // Update sales inventory chart if it exists
    updateSalesInventoryChart();
}
function status_date_wise_search_cash_collection() {
    $('.table-striped').bootstrapTable('refresh');
}
function resetfilters() {
    console.log("Clearing all filters...");

    $('#datepicker').val('');
    $('#start_date').val('');
    $('#end_date').val('');

    // Clear all standard input fields (text, date, number, etc.)
    $('input[type="text"], input[type="search"], input[type="date"], input[type="number"], input[type="email"]').val('');

    // Clear textareas if any
    $('textarea').val('');

    // Clear all select fields
    $('select').each(function () {
        if (this.tomselect) {
            this.tomselect.clear(true);
            this.tomselect.setValue('');
        } else {
            $(this).val('').trigger('change');
        }
    });
    const categorySelect = document.getElementById('categorySelect');
    if (categorySelect && categorySelect.tomselect) {
        categorySelect.tomselect.clear();  // remove selected option
        $('#categorySelect').trigger('change'); // refresh logic if needed
    }
    const statusSelect = document.getElementById('status_filter');
    if (statusSelect && statusSelect.tomselect) {
        statusSelect.tomselect.clear();
    }
    // Refresh Bootstrap table if present
    if ($('#products_table').length && typeof $('#products_table').bootstrapTable === 'function') {
        $('#products_table').bootstrapTable('refresh');
    }

    // Call page-specific refresh logic if available
    if (typeof status_date_wise_search === 'function') {
        status_date_wise_search();
    }

    // Show toast message (if function exists)
    if (typeof showToast === 'function') {
        showToast('All filters cleared successfully!', 'info');
    }

    console.log("All filters cleared.");
}



function mediaParams(p) {
    return {
        'type': $('#media_type').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        'seller_id': $('input[name="seller_id"]').val(),
    };
}

function mediaUploadParams(p) {
    return {
        'type': $('#media-type').val(),
        "start_date": $('#start_date').val(),
        "end_date": $('#end_date').val(),
        "seller_id": $('#seller_id').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function noti_query_params(p) {
    return {
        "message_type": $('#message_type').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function home_query_params(p) {
    return {
        "start_date": $('#start_date').val(),
        "end_date": $('#end_date').val(),
        "order_status": $('#order_status').val(),
        "payment_method": $('#payment_method').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}


function queryParams(p) {
    return {
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function withdrawal_request_query_params(p) {
    return {
        "status": $('#status').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function order_tracking_query_params(p) {
    return {
        "order_id": $('input[name="order_id"]').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function digital_order_mails_query_params(p) {
    return {
        "order_item_id": $('input[name="order_item_id"]').val(),
        "order_id": $('input[name="order_id"]').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function category_query_params(p) {
    return {
        "category_id": $('#category_id').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function product_query_params(p) {
    return {
        "category_id": $('#category_parent').val(),

        "status": $('#status_filter').val(),

        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
$(document).on('change', '#seller_filter, #status_filter, #category_parent, #brand_filter', function () {
    $('#products_table').bootstrapTable('refresh');
});


function stock_query_params(p) {
    return {
        category_id: $('#stock_product_categories').val(),
        // "status": $('#status_filter').val(),
        limit: p.limit,
        offset: p.offset,
        sort: p.sort,
        order: p.order,
        search: p.search,
        seller_id: $('#seller_filter').val(),

    };
}

$(document).on('change', '#stock_product_categories', function () {
    $('#product_stock_table').bootstrapTable('refresh');
});

function wallet_transaction_queryParams(p) {
    return {
        "transaction_type_filter": $('#transaction_type_filter').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function ratingParams(p) {
    return {
        "category_id": $('#category_parent').val(),
        "product_id": $('#product-rating-modal').data('product-id') || null,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function update_theme(update_id, status, table) {

    $.ajax({
        type: 'POST',
        url: base_url + 'seller/themes/switch',
        data: {
            id: update_id,
            status: status,
            table: table
        },
        dataType: 'json',
        success: function (result) {
            if (result['error'] == false) {
                showToast(result.message, "success");

                $('.table').bootstrapTable('refresh');
            } else {
                showToast(result.message, "error");
            }
        }
    });
}
function update_status(update_id, status, table, user) {
    $.ajax({
        type: 'GET',
        url: base_url + user + '/home/update_status',
        data: {
            id: update_id,
            status: status,
            table: table
        },
        dataType: 'json',
        success: function (result) {
            if (result['error'] == true) {
                showToast(result.message + ' Status Updated', "success");
                $('.table').bootstrapTable('refresh');
            } else {
                showToast(result.message + ' Status Not Updated', "error");
            }
        }
    });
}

//product active - deactive status Settings
$(document).on('click', '.update_active_status', function () {

    var update_id = $(this).data('id');
    var status = $(this).data('status');
    var table = $(this).data('table');
    if (table == "themes") {
        update_theme(update_id, status, table, 'seller');
    } else {
        update_status(update_id, status, table, 'seller');
    }

});


function orders_query_params(p) {
    return {
        "start_date": $('#start_date').val(),
        "end_date": $('#end_date').val(),
        "order_status": $('#order_status').val(),
        "user_id": $('#order_user_id').val(),
        "seller_id": $('#order_seller_id').val(),
        "payment_method": $('#payment_method').val(),
        "order_type": $('#order_type').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function transaction_query_params(p) {
    return {
        transaction_type: 'transaction',
        user_id: $('#transaction_user_id').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}


//delete media file 
$(document).on('click', '.delete-media', function () {
    var id = $(this).data('id');

    Notiflix.Confirm.show(
        'Are You Sure!',
        "You won't be able to revert this!",
        'Yes, delete it!',
        'Cancel',
        function okCb() {
            $.ajax({
                type: 'GET',
                url: base_url + from + '/media/delete/' + id,
                dataType: 'json',
                success: function (result) {
                    csrfName = result['csrfName'];
                    csrfHash = result['csrfHash'];
                    if (result['error'] === false) {
                        $('table').bootstrapTable('refresh');
                        $.notify("File Deleted!", { className: "success", globalPosition: "top right" });
                    } else {
                        $.notify(result['message'], { className: "error", globalPosition: "top right" });
                    }
                },
                error: function () {
                    $.notify("Something went wrong. Please try again.", { className: "error", globalPosition: "top right" });
                }
            });
        },
        function cancelCb() {
            $.notify("Your data is safe.", { className: "info", globalPosition: "top right" });
        }
    );
});

//delete product

$(document).on('click', '#delete-product', function () {
    var id = $(this).data('id');

    Notiflix.Confirm.show(
        'Are You Sure!',
        "You won't be able to revert this!",
        'Yes, Delete it!',
        'Cancel',
        function okCb() {
            $.ajax({
                type: 'GET',
                url: base_url + from + '/product/delete_product',
                data: { id: id },
                dataType: 'json',
                beforeSend: function () {
                    Notiflix.Loading.dots('Processing...');
                },
                success: function (response) {
                    Notiflix.Loading.remove();

                    if (response.error === false) {
                        showToast(response.message, 'success');
                    } else {
                        showToast(response.message, 'error');
                    }

                    $('table').bootstrapTable('refresh');
                    csrfName = response['csrfName'];
                    csrfHash = response['csrfHash'];
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    Notiflix.Loading.remove();
                    showToast('Something went wrong. Please try again.', 'error');
                }
            });
        },
        function cancelCb() {
            showToast("Deletion cancelled", 'info');
        },
        {
            okButtonColor: '#fff',
            cancelButtonColor: '#fff',
        }
    );
});

//delete consignment
function delete_consignment(id) {
    Notiflix.Confirm.show(
        'Are You Sure!',
        "You won't be able to revert this!",
        'Yes, delete it!',
        'Cancel',
        function okCb() {
            $.ajax({
                type: "post",
                url: base_url + from + "/orders/delete_consignment",
                data: { id },
                dataType: "json",
                beforeSend: function () {
                    Notiflix.Loading.dots('Deleting...');
                },
                success: function (response) {
                    if (response.error === true) {
                        showToast('error', response.message);
                    } else {
                        response.data.map(val => {
                            $("#product_variant_id_" + val.product_variant_id)
                                .html(JSON.stringify(val));
                        });

                        showToast('success', 'Delete success');
                    }

                    $("#consignment_table").bootstrapTable('refresh');
                },
                complete: function () {
                    Notiflix.Loading.remove();
                }
            });
        },
        function cancelCb() {
            showToast('delete cancelled', 'info');
        }
    );
}


$('.select_single , .multiple_values').each(function () {
    if (typeof TomSelect === 'undefined') {
        $(this).select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    }
});
function salesReport(index, row) {
    return `
        <div class="p-3">
            <p><strong>Order ID:</strong> ${row.id}</p>
            <p><strong>Product Name:</strong> ${row.product_name}</p>
            <p><strong>Payment Method:</strong> ${row.payment_method}</p>
            <p><strong>Final Total:</strong> ${row.final_total}</p>
            <p><strong>Status:</strong> ${row.active_status}</p>
            <p><strong>Date:</strong> ${row.date_added}</p>
        </div>
    `;
}


// Prevent Dropzone auto discover to avoid double initialization
if (window.Dropzone) {
    Dropzone.autoDiscover = false;
}

if (document.getElementById('dropzone')) {

    // If a Dropzone instance already exists for #dropzone, reuse it
    var existingDz = null;
    try {
        existingDz = Dropzone.forElement('#dropzone');
    } catch (e) {
        existingDz = null;
    }

    var myDropzone = existingDz || new Dropzone("#dropzone", {
        url: base_url + from + '/media/upload',
        paramName: "documents",
        autoProcessQueue: false,
        parallelUploads: 12,
        maxFiles: 12,
        autoDiscover: false,
        addRemoveLinks: true,
        timeout: 180000,
        dictRemoveFile: 'x',
        dictMaxFilesExceeded: 'Only 12 files can be uploaded at a time ',
        dictResponseError: 'Error',
        uploadMultiple: true,
        dictDefaultMessage: '<p>  Drag & Drop Media Files Here</p>',
    });

    myDropzone.on("addedfile", function (file) {
        var i = 0;
        if (this.files.length) {
            var _i, _len;
            for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
                if (this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString()) {
                    this.removeFile(file);
                    i++;
                }
            }
        }
    });

    myDropzone.on("error", function (file, response) { });


    myDropzone.on('sending', function (file, xhr, formData) {
        formData.append(csrfName, csrfHash);
        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                var response = JSON.parse(this.response);
                csrfName = response.csrfName;
                csrfHash = response.csrfHash;
                if (response['error'] == false) {
                    Dropzone.forElement('#dropzone').removeAllFiles(true);
                    $("#media-upload-table").bootstrapTable('refresh');
                    showToast(
                        (response['message'], 'success')
                    );
                    $('#media-table').bootstrapTable('refresh');
                } else {


                    showToast(response['message'], 'error');

                }
                $(file.previewElement).find('.dz-error-message').text(response.message);
            }
        };
    });
}


$('#upload-files-btn').on('click', function (e) {
    e.preventDefault();
    myDropzone.processQueue();
    if (myDropzone.files.length === 0) {
        showToast('Please upload at least one file.', 'error');
        return;
    }

});



$(document).ready(function () {
    const statusMap = {
        'bg-primary': 'received',
        'bg-info': 'processed',
        'bg-info-subtle': 'shipped',
        'bg-success': 'delivered',
        'bg-danger': 'cancelled',
        'bg-secondary': 'returned'
    };

    $('.small-box').on('click', function (e) {
        e.stopPropagation();

        let boxClass = $(this).attr('class').split(' ').find(cls => statusMap[cls]);
        let status = statusMap[boxClass];

        if (status) {
            setTimeout(() => {
                $('#order-items-table').bootstrapTable('refreshOptions', {
                    url: base_url + 'seller/orders/view_order_items',
                    query: {
                        order_status: status
                    }
                });
            }, 100);

            let mainContent = document.querySelector('.main-content');
            if (mainContent) {
                mainContent.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            $('#order_status').val(status);
        } else {
            console.warn('No status mapped for class:', boxClass);
        }
    });
});

$(document).on('change', '#deliverable_type', function () {
    var type = $(this).val();
    if (type == "1" || type == "0") {
        $('#deliverable_zipcodes').prop('disabled', 'disabled');
    } else {
        $('#deliverable_zipcodes').prop('disabled', false);
    }
});
// Handle deliverable zipcode type change
$(document).on('change', '#deliverable_zipcode_type, #deliverable_zipcode_type1', function () {

    var type = $(this).val();
    if (type == "1" || type == "0") {
        $('#deliverable_zipcodes').prop('disabled', 'disabled');
        $('#deliverable_zipcodes').closest('.col-md-6').css('opacity', '0.5');
    } else {
        $('#deliverable_zipcodes').prop('disabled', false);
        $('#deliverable_zipcodes').closest('.col-md-6').css('opacity', '1');
    }
});

// Handle deliverable city type change
$(document).on('change', '#deliverable_city_type', function () {
    var type = $(this).val();
    if (type == '1' || type == '0') {
        $('#deliverable_cities').prop('disabled', 'disabled');
        $('#deliverable_cities').closest('.col-md-6').css('opacity', '0.5');
    } else {
        $('#deliverable_cities').prop('disabled', false);
        $('#deliverable_cities').closest('.col-md-6').css('opacity', '1');
    }
});

// Initialize state on page load for profile page and registration page
$(document).ready(function () {
    // Use setTimeout to ensure TomSelect and other elements are initialized first
    setTimeout(function () {
        // For registration page - deliverable_zipcode_type
        if ($('#deliverable_zipcode_type').length) {
            $('#deliverable_zipcode_type').trigger('change');
        }

        // For profile page - deliverable_zipcode_type1
        if ($('#deliverable_zipcode_type1').length) {
            $('#deliverable_zipcode_type1').trigger('change');
        }

        // For both pages - deliverable_city_type
        if ($('#deliverable_city_type').length) {
            $('#deliverable_city_type').trigger('change');
        }
    }, 500);
});
function product_faq_query_params(p) {
    return {
        "product_id": $('#ProductSelect').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function product_rating_query_params(p) {
    return {
        "product_id": $('#ProductSelect').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
$(document).on('change', '#ProductSelect', function () {
    $('#products_faqs_table').bootstrapTable('refresh');
    $('#products_ratings_table').bootstrapTable('refresh');
});




// Offcanvas Form Submit Handler
// Form validation function
function beforeSubmit(form) {
    var isValid = true;

    // Check all required fields
    $(form).find('[required]').each(function () {
        if (!$(this).val() || $(this).val().trim() === '') {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Show validation message if invalid
    if (!isValid) {
        showToast(
            'Please fill all required fields', 'error'

        );
    }

    return isValid;
}

// Remove invalid class on input
$(document).on('input change', '.form-control.is-invalid', function () {
    $(this).removeClass('is-invalid');
});

// Offcanvas Form Submit Handler
$(document).on('submit', '.offcanvas .form-submit-event', function (e) {
    e.preventDefault();

    if (!beforeSubmit(this)) {
        return false;
    }

    var formData = new FormData(this);
    var update_id = $('input[name="update_id"]', this).val();
    var error_box = $('.offcanvas #error_box');
    var submit_btn = $('.offcanvas #submit_btn');
    var btn_html = submit_btn.html();
    var btn_val = submit_btn.val();
    var button_text = (btn_html != '' && btn_html != 'undefined') ? btn_html : btn_val;

    formData.append(csrfName, csrfHash);

    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: formData,
        beforeSend: function () {
            submit_btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Please Wait...');
            submit_btn.attr('disabled', true);
        },
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (result) {
            csrfName = result['csrfName'];
            csrfHash = result['csrfHash'];

            if (result['error'] == true) {
                // Error handling
                if (error_box.length) {
                    error_box.addClass("alert alert-danger").removeClass('d-none alert-success');
                    error_box.html(result['message']).show();
                }

                submit_btn.html(button_text);
                submit_btn.attr('disabled', false);


                showToast(result['message'], "error");

            } else {
                // Success handling
                if (error_box.length) {
                    error_box.addClass("alert alert-success").removeClass('d-none alert-danger');
                    error_box.html(result['message']).show();
                }

                submit_btn.html(button_text);
                submit_btn.attr('disabled', false);


                showToast(result['message'], 'success')

                // Refresh table
                $('.table-striped').bootstrapTable('refresh');

                // Reset form
                $('.offcanvas .form-submit-event')[0].reset();

                // Close offcanvas after success
                setTimeout(function () {
                    var offcanvasElement = document.querySelector('.offcanvas.show');
                    if (offcanvasElement) {
                        var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                        if (bsOffcanvas) {
                            bsOffcanvas.hide();
                        }
                    }
                }, 1000);
            }
        },
        error: function (xhr, status, error) {
            submit_btn.html(button_text);
            submit_btn.attr('disabled', false);


            showToast('An error occurred. Please try again.', 'error');


        }
    });
});

//product name reseting 

if (document.getElementById("product_edit_faq_form") != null && document.getElementById("product_edit_faq_form") != undefined) {

    document.getElementById("product_edit_faq_form").addEventListener("reset", function () {

        // Reset TomSelect selected value
        let tomSelectInstance = document.querySelector("#ProductSelect").tomselect;
        if (tomSelectInstance) {
            tomSelectInstance.clear();      // clears selection
            tomSelectInstance.clearOptions(); // if you want to clear loaded options also (optional)
        }

    });
}
// Optional: Reset form when offcanvas is closed
document.addEventListener('hidden.bs.offcanvas', function (event) {
    if (event.target.querySelector('.form-submit-event')) {
        event.target.querySelector('.form-submit-event').reset();

        // Remove edit hidden fields
        var editInput = event.target.querySelector('input[name="edit_pickup_location"]');
        var updateInput = event.target.querySelector('input[name="update_id"]');
        if (editInput && !editInput.hasAttribute('data-keep')) editInput.remove();
        if (updateInput && !updateInput.hasAttribute('data-keep')) updateInput.remove();

        // Reset title to "Add"
        var titleElement = event.target.querySelector('#offcanvasFormTitle');
        if (titleElement) {
            titleElement.textContent = titleElement.textContent.replace('Edit', 'Add');
        }

        // Hide error box
        var errorBox = event.target.querySelector('#error_box');
        if (errorBox) {
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
        }
    }
});

if ($('#seo_meta_keywords').length) {
    var tags_element = document.querySelector('input[name=seo_meta_keywords]');
    new Tagify(tags_element);
}
if ($('#tags').length) {
    var tags_element = document.querySelector('input[name=tags]');
    new Tagify(tags_element);
}
var bulkSelectedProductIds = [];
$(document).on('click', '#openBulkDeliverabilityOffcanvas', function () {
    // Get selected products from the table
    var selectedProducts = $('#products_deliverability_table').bootstrapTable('getSelections');

    // Check if any products are selected
    if (selectedProducts.length === 0) {
        showToast('Please select at least one product to update.', 'error');
        return;
    }

    // Extract product IDs
    bulkSelectedProductIds = $.map(selectedProducts, function (row) {
        return row.id;
    });

    $('#product_ids').val(bulkSelectedProductIds);

    // Update the count display
    $('#selectedProductCount').text(selectedProducts.length);

    // Reset the form
    $('#bulk_is_in_affiliate').val('');

    // Show the offcanvas
    $('#bulkProductDeliverabilitySetting').offcanvas('show');
});

$(document).on('click', '.editDeliverabilitySetting', function () {

    let productId = $(this).data('id');

    $('#modal_product_id').val(productId);

    window.zipCodeSelect?.clear();
    window.citySelect?.clear();

    $.ajax({
        url: base_url + 'seller/product/get_product_deliverability_details',
        type: 'GET',
        data: { product_id: productId },
        dataType: 'json',
        success: function (res) {

            // ✅ correct condition
            if (res.error) return;

            let data = res.data;

            // ✅ set group types
            $('select[name="deliverable_group_type"]')
                .val(data.deliverable_group_type)
                .trigger('change');

            $('select[name="deliverable_city_group_type"]')
                .val(data.deliverable_city_group_type)
                .trigger('change');

            // ✅ set TomSelect values
            if (data.deliverable_zipcodes_group && window.zipCodeSelect) {
                window.zipCodeSelect.setValue(
                    data.deliverable_zipcodes_group.split(',')
                );
            }

            if (data.deliverable_cities_group && window.citySelect) {
                window.citySelect.setValue(
                    data.deliverable_cities_group.split(',')
                );
            }
        }
    });
});

$(document).on('click', '.sendMailBtn', function (e) {
    e.preventDefault();    
    var email = $(this).data('email');
    var order_id = $(this).data('order_id');
    var order_item_id = $(this).data('order_item_id');
    var username = $(this).data('username');
    
    // Populate form fields
    $('#ManageOrderSendMailModal').find('input[name="order_id"]').val(order_id);
    $('#ManageOrderSendMailModal').find('input[name="order_item_id"]').val(order_item_id);
    $('#ManageOrderSendMailModal').find('input[name="username"]').val(username);
    $('.ManageOrderEmail').val(email);
});