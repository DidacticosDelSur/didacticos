<?php 
include("config.php");

function resizeImage($resourceType,$image_width,$image_height,$resizeWidth,$resizeHeight) {
    // $resizeWidth = 100;
    // $resizeHeight = 100;
    $imageLayer = imagecreatetruecolor($resizeWidth,$resizeHeight);
    imagecopyresampled($imageLayer,$resourceType,0,0,0,0,$resizeWidth,$resizeHeight, $image_width,$image_height);
    return $imageLayer;
}

function redimensionar($ancho,$largo,$path,$ante_imagen,$fichero,$filename,$upload_location)
{
    global $path_fotos;

		$new_width = $ancho;
        $new_height = $largo;
        $fileName = $filename;
		if($_SERVER["HTTP_HOST"] != "localhost")
			$patch_get = $path_fotos;
		else
			$patch_get = $path;
        $sourceProperties = getimagesize($patch_get.$filename);
		
        $resizeFileName = $filename;
        $uploadPath = $upload_location;
        $fileExt = pathinfo($fichero, PATHINFO_EXTENSION);
        $uploadImageType = $sourceProperties[2];
        $sourceImageWidth = $sourceProperties[0];
        $sourceImageHeight = $sourceProperties[1];
        switch ($uploadImageType) {
            case IMAGETYPE_JPEG:
                $resourceType = imagecreatefromjpeg($path.$filename); 
                $imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,$new_width,$new_height);
                $nueva_imgen = imagejpeg($imageLayer,$uploadPath.$ante_imagen.$resizeFileName);
                break;

            case IMAGETYPE_GIF:
                $resourceType = imagecreatefromgif($path.$fileName); 
                $imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,$new_width,$new_height);
                $nueva_imgen = imagegif($imageLayer,$uploadPath.$ante_imagen.$resizeFileName);
                break;

            case IMAGETYPE_PNG:
                $resourceType = imagecreatefrompng($path.$fileName); 
                $imageLayer = resizeImage($resourceType,$sourceImageWidth,$sourceImageHeight,$new_width,$new_height);
                $nueva_imgen = imagepng($imageLayer,$uploadPath.$ante_imagen.$resizeFileName);
                break;

            default:
                $imageProcess = 0;
                break;
        }
        move_uploaded_file($nueva_imgen, $uploadPath. $resizeFileName);
}

$db = mysqli_connect($mysql_host, $mysql_username, $mysql_passwd, $mysql_database);
// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

$id_producto = $_GET["id_producto"];

// Count total files
if(isset($_FILES['files']['name']))
    $countfiles = count($_FILES['files']['name']);
else
    $countfiles = 0;

// Upload directory
$upload_location = $path_fotos;

// To store uploaded files path
$files_arr = array();
$ok = 0;

if($countfiles > 0)
{
// Loop all files
for($index = 0;$index < $countfiles;$index++){

	// File name
	$filename = $_FILES['files']['name'][$index];

	// Get extension
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
    // Valid image extension
    $valid_ext = array("png","jpeg","jpg");

    // Check extension
    if(in_array($ext, $valid_ext)){

    	// File path
		$path = $upload_location.$index."_".$id_producto.".".$ext;
		
        // Upload file
		if(move_uploaded_file($_FILES['files']['tmp_name'][$index],$path)){
			$files_arr[] = $index."_".$id_producto.".".$ext;
			redimensionar(663,600,$path_fotos,"grande_",$_FILES['files']['name'][$index],$index."_".$id_producto.".".$ext,$upload_location);
			redimensionar(101,100,$path_fotos,"peque_",$_FILES['files']['name'][$index],$index."_".$id_producto.".".$ext,$upload_location);
			
			$ok++;
		}
	}
	
	//var_dump($_GET);
			   	
}

if($ok > 0)
{
	$query = "UPDATE productos SET imagen = '".json_encode($files_arr)."' WHERE id = ".$_GET["id_producto"];
	mysqli_query($db,$query);
}
echo json_encode($files_arr);
die;
}