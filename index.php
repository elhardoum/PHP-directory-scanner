<?php


function getDirContents($dir, &$results = array()){
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

$linecount = 0;
$files = $extensions = array();

if( isset( $_REQUEST['dir'] ) ) {

	$files = getDirContents(@$_REQUEST['dir']);
	$allowed = ['js', 'css', 'php'];

	$allowed = isset( $_REQUEST['allowed_extensions'] ) ? (array) $_REQUEST['allowed_extensions'] : array();
	$extensions = array();

	foreach( $files as $i => $file ) {

		$parts = explode( '.', $file );
		$ext = ! empty( end( $parts ) ) && ! is_dir( $file ) ? end( $parts ) : false;

		$extensions[] = $ext;

		if( ( ! in_array( $ext, $allowed ) && ! empty( $allowed ) ) || is_dir( $file ) ) {
			unset( $files[$i] );
		}

	}

	$extensions = array_filter( array_unique( $extensions ) );

	$linecount = 0;

	foreach( $files as $file ) {

		$handle = fopen($file, "r");
		while(!feof($handle)){
		  $line = fgets($handle);
		  $linecount++;
		}
		fclose($handle);

	}

}

?>

<!DOCTYPE html>
<html style="background:#F9F9F9;font-family:monospace,sans-serif">
<head>
	<title></title>
</head>
<body>

	<form method="post">

		<h2 style="color:#555;">Directory to fetch</h2>

		<input type="text" name="dir" value="<?php echo isset( $_REQUEST['dir'] ) ? $_REQUEST['dir'] : ''; ?>" size="100" placeholder="directory path" style="padding: 10px; font-style: italic; color: #555;outline:0"/>

		<?php if( ! empty( $extensions ) ) : ?>

			<h2 style="color:#555;">File extensions to include</h2>

			<?php foreach( $extensions as $ext ) : ?>
				<label><input type="checkbox" value="<?php echo $ext; ?>" <?php echo in_array( $ext, $allowed ) || empty( $allowed ) ? 'checked' : ''; ?> name="allowed_extensions[]" /><?php echo $ext; ?></label>
			<?php endforeach; ?>

		<?php endif; ?>

		<p><button>Fetch</button></p>

	</form>

	<div style="background: #FFF; border: 1px solid #848484; padding: 1em;">

		<h2>Fetched data:</h2>
			
		<?php if( ! empty( $files ) ) : ?>

			<ul>
				<li><em style="text-decoration: underline;">Total files</em>: <strong><?php echo $linecount; ?></strong></li>
				<li><em style="text-decoration: underline;">Total lines found</em>: <strong><?php echo count( $files ); ?></strong></li>
				<li><em style="text-decoration: underline;">Selected directory</em>: <strong><?php echo isset( $_REQUEST['dir'] ) ? $_REQUEST['dir'] : 'none'; ?></strong></li>
				<li><em style="text-decoration: underline;">Files list</em>:<ul><?php foreach( $files as $file ) { echo '<li>' . $file . '</li>'; } ?></ul></li>
			</ul>

		<?php else : ?>

			<p>No files were fetched yet.</p>

		<?php endif; ?>

	</div>

</body>
</html>