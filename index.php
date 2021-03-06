<?php


function getDirContents($dir, &$results = array()){
	
	if( empty( $dir ) ) { return array(); }
    
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
$files = $extensions = $child_dirs = array();

if( isset( $_REQUEST['dir'] ) ) {

	$files = getDirContents(@$_REQUEST['dir']);
	$allowed = ['js', 'css', 'php'];

	$allowed = isset( $_REQUEST['allowed_extensions'] ) ? (array) $_REQUEST['allowed_extensions'] : array();
	$extensions = array();

	foreach( $files as $i => $file ) {

		if( is_dir( $file ) ) {
			$child_dirs[] = $file;
		}

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

	foreach( $child_dirs as $i => $dir ) {

		$_dir = array();
		$_dir['files'] = getDirContents($dir);
		$_dir['lines'] = 0;
		$_dir['path'] = $dir;

		$child_dirs[$i] = $_dir;
	}

	foreach( $child_dirs as $i => $dir ) {

		foreach( $dir['files'] as $file ) {

			$handle = ! is_dir( $file ) ? fopen($file, "r") : false;
			if( ! $handle ) { continue; }
			while(!feof($handle)){
			  $line = fgets($handle);
			  $child_dirs[$i]['lines']++;
			}
			fclose($handle);

		}

	}

}

?>

<!DOCTYPE html>
<html style="background:#F9F9F9;font-family:monospace,sans-serif">
<head>
	<title>PHP directory scanner</title>
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
				<li><em style="text-decoration: underline;">Selected directory</em>: <strong><?php echo isset( $_REQUEST['dir'] ) ? $_REQUEST['dir'] : 'none'; ?></strong></li>
				<li><em style="text-decoration: underline;">Total files</em>: <strong><?php echo count( $files ); ?></strong></li>
				<li><em style="text-decoration: underline;">Total lines found</em>: <strong><?php echo $linecount; ?></strong></li>
				<li><em style="text-decoration: underline;">Total sub-directories</em>: <strong><?php echo count( $child_dirs ); ?></strong></li>
				<li><em style="text-decoration: underline;">Files list</em>:<ol><?php foreach( $files as $file ) { echo '<li>' . $file . '</li>'; } ?></ol></li>
				<li>
					<em style="text-decoration: underline;">Sub-directories:</em><em> (extension filters are not applicable)</em>
					<ol>
						<?php foreach( $child_dirs as $dir ) : ?>
							<li>
								<ul>
									<li>Path: <?php echo $dir['path']; ?></li>
									<li>Total lines found: <?php echo (int) $dir['lines']; ?></li>
									<li>File count: <?php echo count( $dir['files'] ); ?></li>
									<li>Files: <ol>
										<?php foreach( $dir['files'] as $file ) { echo '<li>' . $file . '</li>'; } ?>
									</ol></li>
								</ul>
							</li>
						<?php endforeach; ?>
					</ol>
				</li>
			</ul>

		<?php else : ?>

			<p>No files were fetched yet.</p>

		<?php endif; ?>

	</div>

	<div style="display: table; margin: 0 auto; margin-top: 1em;">
		<p>Copyright &copy; 2016 Samuel Elh | <a href="http://samelh.com">samelh.com</a> | <a href="https://github.com/elhardoum/PHP-directory-scanner">Github project</a></p>
	</div>

</body>
</html>
