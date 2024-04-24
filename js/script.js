    $(document).ready(function () {
        fetchData();
    });

    let dataTable;
    //fetch data from databse
    function fetchData() {
        $.ajax({
            type: "GET",

            url: "db/db_index.php?action=fetch",
            success: function (response) {
                // Destroy the existing DataTable instance if it exists
                if ($.fn.DataTable.isDataTable('#myTable')) {
                    $('#myTable').DataTable().destroy();
                }

                // Initialize DataTable
                dataTable = $('#myTable').DataTable({
                    data: response, // Assuming response is an array of objects
                    columns: [
                        { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
                        { data: 'id', "bVisible": false },
                        { data: 'name' },
                        { data: 'username' },
                        { data: 'password', "bVisible": false },
                        { data: 'email' },
                        { data: 'phone', "bVisible": false },
                        { data: 'dob' },
                        { data: 'address', "bVisible": false },
                        { data: 'gender' },
                        { data: 'country'},
                        { data: 'state' },
                        { data: 'hobbies', "bVisible": false },
                        { data: 'image_url' },
                        {
                            data: null, render: function (data, type, row) {
                                return '<button type="button" class="btn btn-primary m-1 editBtn" data-id="' + row.id + '">Edit</button>' +
                                    '<button type="button" class="btn btn-danger deleteBtn" data-id="' + row.id + '">Delete</button>';
                            }
                        }
                    ]
                });
            }
        });
    }

    //updating data
    $(document).on('click', '.editBtn', function () {
        $('#registrationForm').attr('data-action', 'edit');
        var rowData = dataTable.row($(this).parents('tr')).data();
        var vid = $(this).data("id");
        $('#hiddenId').val(vid);
        $('#username').val(rowData.username);
        $('#name').val(rowData.name);
        $('#email').val(rowData.email);
        $('#phone').val(rowData.phone);
        $('#dob').val(rowData.dob);
        $('#address').val(rowData.address);
        $countryname = rowData.country;
        
        // Fetch and set the country first
        fetchcountrybyid($countryname, function() {
            // Fetch states for the selected country and then set the selected state
            fetchStates(rowData.country, rowData.state);
        });
    
        // Set the selected value for gender
        $('input[name="gender"][value="' + rowData.gender + '"]').prop('checked', true);
    
        // Uncheck all checkboxes first
        $('input[type="checkbox"]').prop('checked', false);
        if (rowData.hobbies) {
            var hobbyList = rowData.hobbies.split(", ");
            hobbyList.forEach(function (hobby) {
                $('#hobby-' + hobby.trim()).prop('checked', true);
            });
        }
    
        $('#regModal').modal('show');
    });
    
    function fetchcountrybyid(cname, callback) {
        if(cname) {
            $.ajax({
                url: './db/getcountry.php',
                type: 'GET',
                data: { country_name: cname },
                dataType: 'json',
                success: function(response) {
                    $.each(response, function (index, country) {
                        $('#country')[0].selectize.addOption({ value: country.id, text: country.country_name });
                        if (country.country_name === cname) {
                            $('#country')[0].selectize.setValue(country.id);
                        }
                    });
                    // Call the callback function after appending country options
                    if (typeof callback === 'function') {
                        callback();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error retrieving country name:", error);
                }
            });
        }
    }
    

function fetchStates(countryName, stateName) {
    if (countryName) {
        $.ajax({
            url: './db/get_state.php',
            type: 'POST',
            data: { country_name: countryName },
            dataType: 'json',
            success: function (response) {
                $('#state')[0].selectize.clearOptions();
                $('#state')[0].selectize.addOption({ value: '', text: 'Select state' });

                $.each(response, function (index, state) {
                    $('#state')[0].selectize.addOption({ value: state.sid, text: state.state_name });
                    if (state.state_name === stateName) {
                        $('#state')[0].selectize.setValue(state.sid);

                    }
                });
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    } else {
        $('#state')[0].selectize.clearOptions();
        $('#state')[0].selectize.setValue('');
    }
}

    


    function updateData(id, formData) {

        $.ajax({
            url: "./db/db_index.php?action=update",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Handle success
                console.log("Data updated successfully:", response);
                $('#regModal').modal('hide');
                dataTable = fetchData();
            },
            error: function (xhr, status, error) {
                // Handle error
                console.error("Error updating data:", error);
                console.log(status);
                console.log(xhr.responseText);
            }
        });
    }


    $(document).on('click', '.addBtn', function () {
        $('#registrationForm').attr('data-action', 'add');
        $('#registrationForm')[0].reset();
        $('#regModal').modal('show');

    })

    function insertData(formData) {
        $.ajax({
            url: "./db/db_index.php?action=insert",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                // Handle success
                console.log(response);
                $('#regModal').modal('hide');

                dataTable = fetchData();
            },
            error: function (xhr, status, error) {
                // Handle error
                console.error(error);
            }
        });
    }
    $('#registrationForm').on('submit', function (event) {

        var form = $(this);

        if (!form[0].checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.addClass('was-validated');
            return;
        }

        // prevent form from submitting normally
        event.preventDefault();

        var hobbiesChecked = form.find('input[type="checkbox"]:checked').length;

        if (hobbiesChecked === 0) {
            form.find('.invalid-feedback .hobbies-feedback').css('display', 'block');
            // event.preventDefault();
            event.stopPropagation();
            console.log("no hobbies selected.");

        } else {
            form.find('.invalid-feedback .hobbies-feedback').hide();
            console.log("at least one hobby selected.");
        }

        console.log("form validated. proceeding to send data.");

        var formData = new FormData(this);
        var hobbies = [];
        form.find('input[type="checkbox"]:checked').each(function () {
            hobbies.push($(this).val());
        });
        console.log("hobbies: " + hobbies.join(', '));
        formData.append('hobbies', hobbies.join(', '));

        var action = form.attr('data-action');
        if (action === 'add') {
            insertData(formData);
        } else if (action === 'edit') {
            var id = $('#hiddenId').val('id');
            updateData(id, formData);
        }

    });

    //deleting data from database
    $('#myTable').on('click', '.deleteBtn', function () {
        let id = $(this).data("id");
        console.log(id);
        deleteData(id);
    });
    //function to deletedatabse
    function deleteData(id) {
        $.ajax({
            url: "db/db_index.php?action=delete&id=" + id,
            method: 'POST',
            action: 'delete',
            dataType: 'text',
            success: function (response) {
                alert('Record deleted successfully.');
                // alert(response);
                fetchData();
            },
            error: function (error) {
                alert('Failed to delete record.');
                console.error(error);
            }
        });
    }

    $('#fileInput').on('change', function (event) {
        var input = event.target;
        var reader = new FileReader();
        reader.onload = function () {
            var preview = $('#imageprev');
            preview.attr('src', reader.result);
        }
        reader.readAsDataURL(input.files[0]);

        // Displaying file path
        var filePath = input.value;
        $('#imagePath').text("File Path: " + filePath);
    });

    
    $(document).ready(function () {
        $('#country').change(function () {
            var countryId = $(this).val(); 
            if (countryId !== "") { 
                $('#state')[0].selectize.clear();
                $.ajax({
                    url: './db/get_states.php',
                    type: 'POST',
                    data: { country_id: countryId }, 
                    dataType: 'json', 
                    success: function (response) { 
                        
                        $('#state')[0].selectize.clearOptions();
                        $.each(response, function (index, state) {
                            $('#state')[0].selectize.addOption({ value: state.sid, text: state.state_name });
                        });
                        $('#state')[0].selectize.refreshOptions(false);
                    },
                    error: function (xhr, status, error) { 
                        console.error(xhr.responseText); 
                    }
                });
            } else {
                // If no country is selected, clear the state dropdown
                $('#state')[0].selectize.clearOptions();
                $('#state')[0].selectize.setValue('');
            }
        });

        // Initialize Selectize on country and state dropdowns
        $('#state').selectize({
            create: false,
            sortField: 'text'
        });
    });

    $(document).ready(function() {
        // Function to fetch country data via AJAX
        function fetchCountries() {
            $.ajax({
                url: './db/get_countries.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Clear existing options
                    $('#country').empty();
    
                    // Add default option
                    $('#country').append('<option selected disabled value="">Select country</option>');
    
                    // Loop through the response and add options to select element
                    $.each(response, function(index, country) {
                        // Append option with country name and set value as country id
                        $('#country').append('<option value="' + country.id + '">' + country.country_name + '</option>');
                    });
    
                    // Initialize selectize plugin after appending options
                    $('#country').selectize({
                        sortField: 'text'
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching countries:', error);
                }
            });
        }
    
        // Call fetchCountries function on document ready
        fetchCountries();
    });
    