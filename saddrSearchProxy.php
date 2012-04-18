<?PHP
if(!defined('SADDR__')) exit(0);

if(isset($_POST['saddrGoSearch'])) {
   if(isset($_POST['saddrGlobalSearch']) &&
         !empty($_POST['saddrGlobalSearch'])) {
      header('Location: index.php?op=doGlobalSearch&search='.
            saddr_urlEncrypt($Saddr, $_POST['saddrGlobalSearch']));
   } else if(isset($_POST['saddrTagSearch']) &&
         !empty($_POST['saddrTagSearch'])) {
      header('Location: index.php?op=doTagSearch&search='.
            saddr_urlEncrypt($Saddr, $_POST['saddrTagSearch']));
   } else {
      header('Location: ' . $_SERVER['HTTP_REFERER']);
   }
}
?>
