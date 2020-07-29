<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$category = $image = $productName = $productCode = $description = "";
$category_err = $image_err = $productName_err = $productCode_err  = $description_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
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
				$image_err = "Please upload a image.";
			  }
		} else {
			$image_err = "Please upload a image.";
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
		$sqlexist = "SELECT count(*) as total FROM product where productCode = '".$productCode."' and productName = '".$productName."' and IsDeleted = 0";
		$result = mysqli_query($link, $sqlexist);
		$row = mysqli_fetch_assoc($result);
		if($row['total'] > 0) {
			$productCode_err = 'Product code has already exist. Please try another.';
			$productName_err = 'Product name has already exist. Please try another.';
		}
	}
    // Check input errors before inserting in database
    if(empty($category_err) && empty($productName_err) && empty($productCode_err) && empty($description_err) && empty($image_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO product (category, image, productName, productCode, description) VALUES (?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssss", $param_category, $param_image, $param_productName, $param_productCode, $param_description);
            
            // Set parameters
            $param_category = $category;
            $param_image = $image;
            $param_productName = $productName;
            $param_productCode = $productCode;
            $param_description = $description;
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
				if(!empty($image)) {
					move_uploaded_file($_FILES["image"]["tmp_name"], $image);
				}
                // Records created successfully. Redirect to landing page
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
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Record</title>
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
                        <h2>Create Record</h2>
                    </div>
                    <p>Please fill this form and submit to add product record to the database.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group <?php echo (!empty($category_err)) ? 'has-error' : ''; ?>">
                            <label>Category</label>
                            <select class="form-control" name="category">
                            <?php foreach($categoryList as $val) { ?>
                                   <option value ="<?php echo $val; ?>"> <?php echo $val; ?> </option>
                            <?php } ?>
                            </select>
                            <span class="help-block"><?php echo $category_err;?></span>
                        </div>
						<div class="form-group <?php echo (!empty($image_err)) ? 'has-error' : ''; ?>">
                            <label>Image</label>
							<input type="file" name="image" class="form-control" value="">
                            <span class="help-block"><?php echo $image_err;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($productName_err)) ? 'has-error' : ''; ?>">
                            <label>Product name</label>
							<input type="text" name="productName" class="form-control" value="<?php echo $productName; ?>">
                            <span class="help-block"><?php echo $productName_err;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($_err)) ? 'has-error' : ''; ?>">
                            <label>Product code</label>
                            <input type="text" name="productCode" class="form-control" value="<?php echo $productCode; ?>">
                            <span class="help-block"><?php echo $productCode_err;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                            <label>Description</label>
                            <textarea name="description" class="form-control"><?php echo $description; ?></textarea>
                            <span class="help-block"><?php echo $description_err;?></span>
                        </div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-default">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>