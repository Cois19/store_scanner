<?php
include 'database/connect.php';
include 'users/session.php';

$doId = $_GET['id'];
$hideIfNot1 = '';

$query4 = "SELECT * FROM tdoc WHERE que = $doId";
$result4 = mysqli_query($conn, $query4);
if ($result4 && mysqli_num_rows($result4) > 0) {
    $row4 = mysqli_fetch_assoc($result4);
    $tdono = $row4['tdono'];

    if ($row4['tstatus'] != 1) {
        $hideIfNot1 = "d-none";
    }
} else {
    
    // header("Location: do_list.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order Details</title>
    <?php include 'scripts.php' ?>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navbar.php' ?>
    <div class="mb-3"></div>
    <div class="container">
        <!-- Modals -->
        <?php include 'modals/create.php'; ?>
        <?php include 'modals/edit.php'; ?>
        <?php include 'modals/uploading.php'; ?>
        <?php include 'modals/isn.php'; ?>
        <?php include 'modals/resetM.php'; ?>
        <?php include 'modals/deleteDoM.php'; ?>
        <?php include 'modals/grM.php'; ?>
        <?php include 'modals/changePassM.php'; ?>

        <div class="card border-dark mb-3" id="doCard">
            <div class="card-header">
                Delivery Order
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Pallet ID</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tpid']; ?>
                        </p>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Part No</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tpno']; ?>
                        </p>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Part Name</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tpname']; ?>
                        </p>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">DN Number</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tdono']; ?>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Quantity</h5>
                        <p class="card-text mb-2" id="qtyCount">
                            <?php echo $row4['tqty']; ?>
                        </p>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Model</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tpmodel']; ?>
                        </p>
                    </div><div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Date</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tdate']; ?>
                        </p>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Vendor</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tvendor']; ?>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <h5 class="card-title">Box Count</h5>
                        <p class="card-text mb-2">
                            <?php echo $row4['tbxcount']; ?>
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-between">
                    <div class="btn-group mb-3 mb-lg-0 <?php echo $hideIfNot1 ?>">
                        <button id="scanIsnBtn" href="#" data-bs-toggle="modal" data-bs-target="#isnModal"
                            class="btn btn-primary">Scan
                            ISN</button>
                        <button id="grBtn" href="#" data-bs-toggle="modal" data-bs-target="#grModal"
                            class="btn btn-success" disabled>Good Received</button>
                    </div>
                    <div class="btn-group <?php echo $hideIfNot1 ?>">
                        <button href="#" data-bs-toggle="modal" data-bs-target="#resetModal" class="btn btn-danger"
                            <?php echo ($utype == 3) ? 'disabled' : ''; ?>>Reset
                            ISN</button>
                        <button href="#" data-bs-toggle="modal" data-bs-target="#deleteDoModal" class="btn btn-danger"
                            <?php echo ($utype != 1) ? 'disabled' : ''; ?>>Delete
                            DO</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="isnTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ISN</th>
                        <th>PART NO</th>
                        <th>MODEL</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <script>
        

        $('#newIsnForm').submit(function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/insertISN.php',
                data: $(this).serialize() + '&doId=<?php echo $doId; ?>',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        $('#newIsnForm')[0].reset();
                        updateQtyCount();
                        loadTable();
                    }
                    else if (response.status == 'fail') {
                        alert('Duplicate Data');
                    } else if (response.status == 'empty') {
                        alert('ISN Cannot be Empty!');
                    } else {
                        alert('Failed');
                    }
                }
            });
        });

        $('#resetBtn').click(function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/reset.php',
                data: { tdono: <?php echo "'" . $tdono . "'"; ?> },
                success: function (response) {
                    if (response == 'success') {
                        $('#resetModal').modal('hide');
                    } else if (response == 'fail') {
                        alert('Reset Failed');
                    } else if (response == 'unauthorized') {
                        alert('You are not authorized');
                    }
                    updateQtyCount();
                    loadTable();
                }
            });
        });

        $('#deleteBtn').click(function (e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/delete.php',
                data: { tdono: <?php echo "'" . $tdono . "'"; ?> },
                success: function (response) {
                    if (response == 'success') {
                        $('#deleteDoModal').modal('hide');
                        var url = 'do_list.php';

                        window.location.href = url;
                    } else if (response == 'fail') {
                        alert('Reset Failed');
                    } else if (response == 'unauthorized') {
                        alert('You are not authorized');
                    }
                }
            });

        });

        $('#grConfirm').click(function () {
            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/gr.php',
                data: { tdono: <?php echo "'" . $tdono . "'"; ?> },
                success: function (response) {
                    if (response == 'success') {
                        $('#grModal').modal('hide');
                        var url = 'do_list.php';

                        window.location.reload();
                    } else if (response == 'fail') {
                        alert('Reset Failed');
                    } else if (response == 'unauthorized') {
                        alert('Remaining Qty is more than 0');
                    }
                }
            });
        });

        function updateQtyCount() {
            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/updateQtyCount.php',
                data: { doId: <?php echo $doId; ?> },
                dataType: 'json',
                success: function (response) {
                    // $('#qtyCount').text(response);
                    var remainingQty = $('.remaining-qty');
                    remainingQty.text('Remaining : ' + response.remainingQty);
                    remainingQty.addClass('text-danger fw-bold border border-danger rounded m-auto px-2');

                    var scannedQty = $('.scanned-qty');
                    scannedQty.text('Scanned : ' + response.scannedQty);
                    scannedQty.addClass('text-success fw-bold border border-success rounded m-auto px-2');
                    if (response.remainingQty == 0) {
                        document.getElementById("scanIsnBtn").disabled = true;
                        document.getElementById("grBtn").disabled = false;
                        $('#isnModal').modal('hide');
                        alert('Scanning Complete!');
                    } else {
                        document.getElementById("scanIsnBtn").disabled = false;
                        document.getElementById("grBtn").disabled = true;
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        }

        var doId = <?php echo $doId; ?>;

        function loadTable() {
            $.ajax({
                type: 'POST',
                url: 'dbCrudFunctions/table.php',
                data: { mode: 'isn', doId: doId },
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

        var table = $('#isnTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between"B<"d-flex flex-wrap me-3"<"remaining-qty me-2"><"scanned-qty">><"d-flex flex-wrap justify-content-between"<"me-3"l>f>>rt<"d-flex flex-wrap justify-content-between"ip>',
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
            order: [[0, 'desc']],
            columnDefs: [
                {
                    target: 0,
                    visible: false,
                    searchable: false
                },
            ],
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 }
            ]
        });

        $(document).ready(function () {
            updateQtyCount();
            loadTable();
        });

        <?php include 'dbCrudFunctions/bodyScripts.js' ?>
    </script>
</body>

<?php mysqli_close($conn); ?>

</html>