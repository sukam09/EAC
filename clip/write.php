<?php

include 'db.php';

session_start();

$user_id = $_SESSION[SSN];
$sql = "SELECT * FROM session WHERE SSN = '{$user_id}'";
$ret = mysql_query( $sql );

if( $ret ) {
?>

<!DOCTYPE html>

<html>

  <head>

    <title> 게시판 </title>



    <!-- Bootstrap core CSS -->

    <link href="bootstrap-3.3.2/dist/css/bootstrap.min.css" rel="stylesheet">



    <!-- Custom styles for this template -->

    <link href="bootstrap-3.3.2/dist/css/jumbotron.css" rel="stylesheet">

  </head>



  <body>



    <nav class="navbar navbar-inverse navbar-fixed-top">

      <div class="container">

        <div class="navbar-header">

          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">

            <span class="sr-only">Toggle navigation</span>

            <span class="icon-bar"></span>

            <span class="icon-bar"></span>

            <span class="icon-bar"></span>

          </button>

          <a class="navbar-brand" href="#"> 게시판 </a>

        </div>

        <div id="navbar" class="navbar-collapse collapse">

<?php

  if( !isset($_SESSION[is_login]) && $_SESSION[in_login] != 1 ) {

?>

          <form class="navbar-form navbar-right" method=POST action=signin.php>

            <div class="form-group">

              <input type="text" name=ID placeholder="USER ID" class="form-control">

            </div>

            <div class="form-group">

              <input type="password" name=PW placeholder="Password" class="form-control">

            </div>

            <button type="submit" class="btn btn-success">Sign in</button>

          </form>

<?php

  } else {

?>



        <form class="navbar-form navbar-right" method=POST action=signout.php>

          <button type="submit" class="btn btn-success">Sign out</button>

        </form>



<?php

  }

?>



    </form>

        </div><!--/.navbar-collapse -->

      </div>

    </nav>



    <!-- Main jumbotron for a primary marketing message or call to action -->

    <div class="jumbotron">

      <div class="container">

        <form class="form-horizontal" method=POST action=write_ok.php>

          <div class="form-group">

            <label for="inputEmail3" class="col-sm-2 control-label">제목</label>

              <div class="col-sm-10">

                <input type="text" name=title class="form-control" id="inputEmail3">

              </div>

          </div>

        <label for="inputEmail3" class="col-sm-2 control-label">게시글</label>

        <div class="col-sm-offset-2 col-sm-10">

            <textarea name=body class="form-control" rows="10"></textarea>

        </div>

      </div>

      <div class="form-group">

        <div class="col-sm-offset-2 col-sm-10">

          <button type="submit" class="btn btn-default">작성 완료</button>

            </div>

           </div>

         </form>

      </div>

    </div>



    <footer>

      <p>&copy; made 20170823</p>

    </footer>



  </body>

</html>

<?php

}else {

  echo "<script> alert('로그인한 사용자만 글 작성이 가능합니다'); </script>";
// .5초 후에 원래 페이지로 리다이렉트
echo "<meta http-equiv=\"refresh\" content=\".5;url=".$_SERVER['HTTP_REFERER']."\"/>"; 
}

?>
