<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$category = $image = $productName = $productCode = $description = "";
$category_err = $image_err = $productName_err = $productCode_err  = $description_err = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input value
    $id = $_POST["id"];
    
    // Validate category
    $input_category = trim($_POST["category"]);
    if(empty($input_category)){
        $category_err = "Please select a category.";
    } else{
        $category = $input_category;
    }
    
	// Validate image
	if(isset($_FILES['image']['name'])) {
		if(!empty($_FILES['image']['name'])) {
			$target_dir = "products/";
			$target_file = $target_dir . basename($_FILES["image"]["name"]);
			$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			  $filename = $target_dir . uniqid().".".$imageFileType;
			  $check = getimagesize($_FILES["image"]["tmp_name"]);
			  if($check !== false) {
				$image = $filename;
			  } else {
				$image_err = "";
			  }
		} else {
			$image_err = "";
		}
	} 

	
    // Validate product name
    $input_productName = trim($_POST["productName"]);
    if(empty($input_productName)){
        $productName_err = "Please enter an product name.";     
    } else{
        $productName = $input_productName;
    }
    
    // Validate code
    $input_productCode = trim($_POST["productCode"]);
    if(empty($input_productCode)){
        $productCode_err = "Please enter the product code";     
    } else{
        $productCode = $input_productCode;
    }
    
    // Validate description
    $input_description = trim($_POST["description"]);
    if(empty($input_description)){
        $description_err = "Please enter an description.";     
    } else{
        $description = $input_description;
    }
	// check if record has duplicate product name and code
    if(!empty($productCode) && !empty($productName)) {
		$sqlexist = "SELECT count(*) as total FROM product where productCode = '".$productCode."' and productName = '".$productName."' and IsDeleted = 0 and id != ".$id;
		$result = mysqli_query($link, $sqlexist);
		$row = mysqli_fetch_assoc($result);
		//echo $sqlexist; 
		//print_r($row);
	//exit;
		if($row['total'] > 0) {
			$productCode_err = 'Product code has already exist. Please try another.';
			$productName_err = 'Product name has already exist. Please try another.';
		}
	}
    // Check input errors before inserting in database
        if(empty($category_err) && empty($productName_err) && empty($productCode_err) && empty($description_err) && empty($image_err)){
        // Prepare an update statement
		if(!empty($image)) {
			$sql = "UPDATE product SET category=?, productName=?, productCode=?, description=?, image=? WHERE id=?";
		} else {
			$sql = "UPDATE product SET category=?, productName=?, productCode=?, description=? WHERE id=?";
		}	
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
			if(!empty($image)) {
				mysqli_stmt_bind_param($stmt, "sssssi", $param_category, $param_productName, $param_productCode, $param_description, $param_image, $param_id);
			} else {
				mysqli_stmt_bind_param($stmt, "ssssi", $param_category, $param_productName, $param_productCode, $param_description, $param_id);
			}
            // Set parameters
            $param_category = $category;
            $param_productName = $productName;
            $param_productCode = $productCode;
            $param_description = $description;
            $param_image = $image;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
				if(!empty($image)) {
					move_uploaded_file($_FILES["image"]["tmp_name"], $image);
				}
                // Records updated successfully. Redirect to landing page
                header("location: index.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM product WHERE id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $category = $row["category"];
                    $productName = $row["productName"];
                    $productCode = $row["productCode"];
                    $description = $row["description"];
                    $image = $row["image"];
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
        
        // Close connection
        mysqli_close($link);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        .wrapper{
            width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h2>Update Record</h2>
                    </div>
                    <p>Please edit the input values and submit to update the record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post"  enctype="multipart/form-data">
                        <div class="form-group <?php echo (!empty($category_err)) ? 'has-error' : ''; ?>">
                            <label>Category</label>
                            <input type="text" name="category" class="form-control" value="<?php echo $category; ?>">
                            <span class="help-block"><?php echo $category_err;?></span>
                        </div>
						<div ><img src="<?php echo $image; ?>" width="100"></div>
						<div class="form-group <?php echo (!empty($image_err)) ? 'has-error' : ''; ?>">
                            <label>Image</label>
							<input type="file" name="image" class="form-control" value="">
                            <span class="help-block"><?php echo $image_err;?></span>
                        </div>
						<div class="form-group <?php echo (!empty($productName_err)) ? 'has-error' : ''; ?>">
                            <label>Product Name</label>
                            <input type="text" name="productName" class="form-control" value="<?php echo $productName; ?>">
                            <span class="help-block"><?php echo $productName_err;?></span>
                        </div>
						<div class="form-group <?php echo (!empty($productCode_err)) ? 'has-error' : ''; ?>">
                            <label>Product Code</label>
                            <input type="text" name="productCode" class="form-control" value="<?php echo $productCode; ?>">
                            <span class="help-block"><?php echo $productCode_err;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                            <label>Description</label>
                            <textarea name="description" class="form-control"><?php echo $description; ?></textarea>
                            <span class="help-block"><?php echo $description_err;?></span>
                        </div>
                        
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-default">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>