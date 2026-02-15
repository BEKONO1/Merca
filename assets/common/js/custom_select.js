"use strict";

function objectToQueryParams(obj) {
    return Object.entries(obj)
        .map(([key, val]) => encodeURIComponent(key) + '=' + encodeURIComponent(val))
        .join('&');
}




function initTomSelect({
    element,
    url,
    placeholder = "Select option",
    onItemAdd = null, // 👈 For add new option 
    preselected = null, //👈 edit time preselect 
    offcanvasId = null, // 👈 add this option
    dataAttribute = null, // 👈 generic field mapping
    maxItems = 1,
    switchModal = null, // 👈 optional: { from: '#AddZipcodeModal', to: '#AddCityModal' }
    preloadOptions = false // 👈 new parameter for preloaded options
}) {

    let ts = new TomSelect(element, {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        preload: preloadOptions, // Use the parameter instead of hardcoded true
        maxItems: maxItems,
        maxOptions: 5,
        placeholder: placeholder,
        
        load: function (query, callback) {

            // If no query and preloadOptions is true, load initial options


            if (!query && preloadOptions) {

                axios.get(url)
                    .then(response => {
                        const json = response.data;

                        // console.log(json);

                        let addNew = [{ id: '__addnew__', text: '+ Add New' }];
                        if (onItemAdd === null) {
                            addNew = [];
                        }
                        let filtered = json.filter(i => i.id !== '__addnew__');
                        callback([...addNew, ...filtered]);

                        // handle preselected

                        if (preselected) {
                            setTimeout(() => {
                                if (ts.getValue().length === 0) {
                                    ts.setValue(Array.isArray(preselected) ? preselected : [preselected], false);
                                }
                            }, 200);
                        }
                    })
                    .catch(() => {
                        callback();
                    });
                return;
            }

            // Handle search queries - always make API call for search
            const obj = { search: query };


            if (preselected !== null) {
                obj["selected"] = preselected;
            }

            const queryString = objectToQueryParams(obj);

         
            const separator = url.includes('?') ? '&' : '?';
            axios.get(url + separator + queryString)
            // axios.get(url + '&' + queryString)
                .then(response => {
                    const json = response.data;
                    let addNew = [{ id: '__addnew__', text: '+ Add New' }];
                    if (onItemAdd === null) {
                        addNew = [];
                    }
                    let filtered = json.filter(i => i.id !== '__addnew__');
                    callback([...addNew, ...filtered]);

                    // handle preselected
                    if (preselected && ts.getValue() === "") {
                        if (Array.isArray(preselected)) {
                            ts.setValue(preselected, false);
                        } else {
                            ts.setValue([preselected], false);
                        }
                    }
                })
                .catch(() => {
                    callback();
                });
        },
        onItemAdd: function (value) {

            if (value === "__addnew__") {
                this.clear(); // remove the fake option

                if (typeof onItemAdd === "function") {
                    onItemAdd(); // call your handler (open modal, etc.)
                }

                // optional modal switching
                if (switchModal && switchModal.from && switchModal.to) {
                    $(switchModal.from).modal('hide');
                    $(switchModal.to).modal('show');
                }
            } else {

            }
        }

    });

    // 👇 listen for zipcode type change outside of Alpine
    // $(document).on('change', '#deliverable_zipcode_type, #delivery_city_type, #deliverable_type', function () {
    //     let type = $(this).val();
    //     // console.log("Zipcode type changed to:", type);

    //     if (type == "1" || type == "0") {
    //         ts.disable();
    //     } else {
    //         ts.enable();
    //     }
    // });

    // ✅ Handle preselected when no API (static select)
    if (!url && preselected) {
        setTimeout(() => {
            if (Array.isArray(preselected)) ts.setValue(preselected, false);
            else ts.setValue([preselected], false);
        }, 100);
    }

    // 👇 attach offcanvas event if provided
    if (offcanvasId) {
        document.getElementById(offcanvasId)
            .addEventListener('show.bs.offcanvas', (event) => {
                const button = event.relatedTarget;
                const value = button.getAttribute(dataAttribute);

                if (value) {
                    const values = value.split(',').map(v => v.trim()).filter(v => v !== "");
                    ts.setValue(values, true);
                } else {
                    ts.setValue(null, true);
                }
            });
    }

    let el = element instanceof jQuery ? element[0] : element;
    if (el.id) {
        window[el.id] = ts;
    }
    
    return ts;
}

// simple modal openers
function openNewCityModal() {
    $('#AddCityModal').modal('show');
}
function openNewZipcodeModal() {
    $('#AddZipcodeModal').modal('show');
}
function addBlogCategoryModal() {
    $('#addBlogCategoryModal').modal('show');
}
function addCategoryModal() {
    $('#addCategoryModal').modal('show');
}
function addAttributeSetModal() {
    $('#addAttributeSetModal').modal('show');
}
function addAttributeModal() {
    $('#addAttributeModal').modal('show');
}


