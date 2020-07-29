<?php 
// Include config file
    require_once "config.php";
    $where = '';
	$searchCategory = '';
	if(isset($_REQUEST["searchCategory"])) {
		$where = ' and category = "'.$_REQUEST["searchCategory"].'"';
		$searchCategory = $_REQUEST["searchCategory"];
	}
                                    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js"></script>
    <style type="text/css">
        .wrapper{
            width: 650px;
            margin: 0 auto;
        }
        .page-header h2{
            margin-top: 0;
        }
        table tr td:last-child a{
            margin-right: 15px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
			    $("select#category").change(function(){
                var selectedCategory = $(this).children("option:selected").val();
				if(selectedCategory !="") {
					window.location.href = 'index.php?searchCategory='+selectedCategory;
				}
				});

        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header clearfix">
                        <h2 class="pull-left">Products Details</h2>
                        <a href="create.php" class="btn btn-success pull-right">Add New Product</a>
						
                    </div>
					<div class="page-header clearfix">
						<label>Category</label>
                            <select class="form-control" name="category" id="category">
                            <?php foreach($categoryList as $val) { 
								$selected = ($searchCategory == $val)? 'selected': '';
							?>
                                   <option <?php echo $selected; ?> value ="<?php echo $val; ?>"> <?php echo $val; ?> </option>
                            <?php } ?>
                            </select>
							                        <a href="index.php" class="btn btn-success pull-right">Refresh</a>

                    </div>
                    <?php
					$sql = "SELECT * FROM product where IsDeleted = 0".$where;
					// Attempt select query execution
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo "<table class='table table-bordered table-striped'>";
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th>#</th>";
										echo "<th>Image</th>";
                                        echo "<th>Category</th>";
                                        echo "<th>Product Name</th>";
                                        echo "<th>Product Code</th>";
                                        echo "<th>Description</th>";
                                        echo "<th>Action</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
								$i=0;
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        echo "<td>" . ++$i . "</td>";
                                        echo "<td><img width='100' src='". $row['image'] . "'> </td>";
                                        echo "<td>". $row['category'] . " </td>";
                                        echo "<td>" . $row['productName'] . "</td>";
                                        echo "<td>" . $row['productCode'] . "</td>";
                                        echo "<td>" . $row['description'] . "</td>";
                                        echo "<td>";
                                            echo "<a href='read.php?id=". $row['id'] ."' title='View Record' data-toggle='tooltip'><span class='glyphicon glyphicon-eye-open'></span></a>";
                                            echo "<a href='update.php?id=". $row['id'] ."' title='Update Record' data-toggle='tooltip'><span class='glyphicon glyphicon-pencil'></span></a>";
                                            echo "<a href='delete.php?id=". $row['id'] ."' title='Delete Record' data-toggle='tooltip'><span class='glyphicon glyphicon-trash'></span></a>";
                                        echo "</td>";
                                    echo "</tr>";
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            echo "<p class='lead'><em>No records were found.</em></p>";
                        }
                    } else{
                        echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
                    }
 
                    // Close connection
                    mysqli_close($link);
                    ?>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>