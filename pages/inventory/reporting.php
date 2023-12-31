<?php
// phpinfo();
include '../../database/connect.php';
include '../../users/session.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Tag Summary</title>
    <?php include '../../scripts.php' ?>
</head>

<body>
    <!-- Navbar -->
    <?php include '../../navbar.php' ?>
    <div class="mb-3"></div>
    <div class="container">

        <!-- Modals -->
        <?php include '../../modals/delivery_order/create.php'; ?>
        <?php include '../../modals/delivery_order/isn.php'; ?>
        <?php include '../../modals/changePassM.php'; ?>
        <?php include '../../modals/inventory/addPeriodM.php'; ?>
        <?php include '../../modals/inventory/editTagM.php'; ?>
        <?php include '../../modals/loadingSpinnerM.php'; ?>
        <?php include '../../modals/inventory/deleteTagM.php'; ?>

        <h2>Summary</h2>
        <hr>
        <form method="post" id="filterForm">
            <div class="row">
                <div class="col-3">
                    <label for="periodFilter"><strong>Period :</strong></label>
                    <div id="periodFilterParent">
                        <select class="form-select" name="periodFilter" id="periodFilter">
                            <option disabled selected>Select Period</option>
                            <?php
                            $sql1 = "SELECT que, periodname FROM tperiod ORDER BY que DESC";
                            $periods = mysqli_query($conn, $sql1);
                            while ($period = mysqli_fetch_array($periods, MYSQLI_ASSOC)):
                                ;
                                ?>
                                <option value="<?php echo $period['que']; ?>">
                                    <?php echo $period['periodname']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <label for="periodFilter"><strong>Area :</strong></label>
                    <div id="areaFilterParent">
                        <select class="form-select" name="areaFilter" id="areaFilter">
                            <option disabled selected>Select Area</option>
                            <option value="">ALL</option>
                            <?php
                            $sql1 = "SELECT areacode, areaname FROM tarea ORDER BY que";
                            $periods = mysqli_query($conn, $sql1);
                            while ($period = mysqli_fetch_array($periods, MYSQLI_ASSOC)):
                                ;
                                ?>
                                <option value="<?php echo $period['areaname']; ?>">
                                <?php echo $period['areacode']; ?> - <?php echo $period['areaname']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
        </form>
        <br>
        <div class="table-responsive">
            <table id="reportingTable" class="table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>QUE</th>
                        <th>TAG NO</th>
                        <th>OWNER</th>
                        <th>AREA CODE</th>
                        <th>AREA NAME</th>
                        <th>SUB LOC</th>
                        <th>ACCOUNT</th>
                        <th>MODEL</th>
                        <th>PART NO</th>
                        <th>PART DESC</th>
                        <th>QTY</th>
                        <th>UOM</th>
                        <th>REMARKS</th>
                        <th>DATE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
            </table>
        </div>
        <!-- <?php include '../../footer.php' ?> -->
    </div>

    <script>
        $('#periodFilter, #areaFilter').on('change', function () {
            loadTableWithFilter();
        });

        function loadReportingTable() {
            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/table.php',
                data: { mode: 'reporting' },
                dataType: 'json',
                success: function (response) {
                    if (response.data) {
                        table.clear().rows.add(response.data).draw();
                    } else {
                        table.clear().draw();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        function loadTableWithFilter() {
            // Call the submitForm function to submit the form
            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/table.php',
                data: $('#filterForm').serialize() + '&mode=reporting',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'timeout') {
                        window.location.href = '/vsite/cms/users/login.php';
                    } else if (response.status == 'period') {
                        alert(response.message);
                    } else {
                        if (response.data !== null) {
                            table.clear().rows.add(response.data).draw();
                        } else {
                            table.clear().draw();
                        }
                    }

                }
            });
        }

        function editmodal(id) {

            //THIS IS JQUERY AJAX METHOD
            $.ajax({
                type: 'GET',
                url: 'edit.php',
                data: { queryid: id, mode: 'edittag' },
                success: function (data) {
                    $("#display_detailsedit").html(data); //the data is displayed in id=display_details
                }
            });
        }

        $('#editTagForm').submit(function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: '/vsite/cms/pages/inventory/dbCrudFunctions/update.php',
                data: $(this).serialize(),
                success: function (response) {
                    if (response == 'timeout') {
                        window.location.href = '/vsite/cms/users/login.php';
                    } else if (response == 'success') {
                        $('#editTagModal').modal('hide');
                        loadTableWithFilter();
                    } else {
                        alert('Failed');
                    }
                }
            });
        });

        // Move this event binding outside both functions and ensure it's bound only once
        $('#confirmDeleteTagBtn').on('click', function () {
            var tagToDelete = $(this).data('tag'); // Get the Tag from the data attribute
            $('#deleteTagModal').modal('hide');
            confirmDeleteTag(tagToDelete);
        });

        function deleteTag(tag) {
            $('#deleteTagModal').modal('show');
            $('#confirmDeleteTagBtn').data('tag', tag); // Store the Tag in the data attribute
        }

        function confirmDeleteTag(tag) {
            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/deleteRowTag.php',
                data: { tag: tag },
                success: function (response) {
                    if (response == 'success') {
                        // $('#deleteISNModal').modal('hide');
                    } else if (response == 'fail') {
                        alert('Delete Failed');
                    } else if (response == 'unauthorized') {
                        alert('Ask Admin Level User to Delete.');
                    } else if (response == 'timeout') {
                        window.location.href = '/vsite/cms/users/login.php';
                    }
                    loadTableWithFilter();
                }
            });
        }

        var table = $('#reportingTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between"B<"d-flex flex-wrap justify-content-between"<"me-3"l>f>>rt<"d-flex flex-wrap justify-content-between"ip>',
            buttons: [
                {
                    extend: 'collection',
                    text: 'Export',
                    buttons: [
                        'copy',
                        'excel',
                        'csv',
                        'pdf',
                        'print'
                    ]
                }
            ],
            // order: [[0, 'desc']],
            columnDefs: [
                {
                    target: 14,
                    visible: false,
                    searchable: false
                },
                {
                    target: 1,
                    visible: false,
                    searchable: false
                }
            ],
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        var startNumber = 1;
                        var currentNumber = meta.row + startNumber;
                        return currentNumber;
                    }
                },
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6 },
                { data: 7 },
                { data: 8 },
                { data: 9 },
                { data: 10 },
                { data: 11 },
                { data: 12 },
                { data: 13 },
                {
                    data: null,
                    render: function (data, type, row) {
                        var token = row[0];
                        var href = '/vsite/cms/pages/inventory/inventory_tag.php?id=' + token;
                        return '<div class="btn-group"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTagModal" onClick="editmodal(\'' + token + '\')">EDIT</button>' +
                            '<a href="' + href + '" target="_blank"><button type="button" class="btn btn-sm btn-success" href="">PRINT</button></a>' + 
                            '<button type="button" class="btn btn-sm btn-danger" onClick="deleteTag(\'' + token + '\')">DELETE</button></div>';
                    }
                }
            ]
        });

        $('#periodFilter').select2({
            dropdownParent: $('#periodFilterParent'),
            width: '100%'
        });

        $('#areaFilter').select2({
            dropdownParent: $('#areaFilterParent'),
            width: '100%',
        });

        $(document).ready(function () {
            // loadReportingTable();
        });

        <?php include '../../dbCrudFunctions/bodyScripts.js' ?>
    </script>
    <?php include '../../styles/tableOverride.php' ?>
</body>

<?php mysqli_close($conn); ?>

</html>