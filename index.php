<?php
ini_set('mbstring.internal_encoding' , 'UTF-8');

//タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

//変数の初期化
$now_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$clean = array();

session_start();

include 'db_check.php';

if (!empty($_POST['btn_submit'])){

    // 表示名の入力チェック
	if( empty($_POST['view_name']) ) {
		$error_message[] = '表示名を入力してください。';
    }else {
        $clean['view_name'] = htmlspecialchars( $_POST['view_name'], ENT_QUOTES);
            
        // セッションに表示名を保存
		$_SESSION['view_name'] = $clean['view_name'];
	}
    
    // メッセージの入力チェック
	if( empty($_POST['message']) ) {
		$error_message[] = 'ひと言メッセージを入力してください。';
    }else {
		$clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
    }

    if( empty($error_message) ) { 

     //データベース接続
     $mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);

   // 接続エラーの確認
		if( $mysqli->connect_errno ) {
			$error_message[] = '書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
		} else {
			
			// 書き込み日時を取得
			$now_date = date("Y-m-d H:i:s");
			
			// データを登録するSQL作成
			$sql = "INSERT INTO message (view_name, message, post_date) VALUES ( '$clean[view_name]', '$clean[message]', '$now_date')";
			
			// データを登録
			$res = $mysqli->query($sql);
		
			if( $res ) {
				$success_message = 'メッセージを書き込みました。';
			} else {
				$error_message[] = '書き込みに失敗しました。';
			}
		
			// データベースの接続を閉じる
			$mysqli->close();
    }
  }
}


// データベースに接続
$mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 接続エラーの確認
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {
    // ここにデータを取得する処理が入る
    $sql = "SELECT view_name,message,post_date,modified FROM message ORDER BY post_date DESC";
	$res = $mysqli->query($sql);
	
	if( $res ) {
		$message_array = $res->fetch_all(MYSQLI_ASSOC);
	}
	
	$mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<link rel="stylesheet" href="text.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>トップページ</title>

</head>
<body>
<form>
<input type="button" onClick="location.href='admin.php'" value="管理者画面へ">
</form>
<h1>トップページ</h1>
<!-- ここにメッセージの入力フォームを設置 -->
<?php if( !empty($success_message) ): ?>
    <p class="success_message"><?php echo $success_message; ?></p>
<?php endif; ?>
<?php if( !empty($error_message) ): ?>
	<ul class="error_message">
		<?php foreach( $error_message as $value ): ?>
			<li>・<?php echo $value; ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post">
  <div>
    <label for="view_name">投稿者</label>
    <input id="view_name" type="text" name="view_name" value="<?php if( !empty($_SESSION['view_name']) ){ echo $_SESSION['view_name']; } ?>">
  </div>
  <div>
    <label for="message">内容</label>
    <textarea id="message" name="message"></textarea>  
  </div> 
  <input type="submit" name="btn_submit" value="投稿">
</form>  
<hr>
<section>
<!-- ここに投稿されたメッセージを表示 -->   
<?php if( !empty($message_array) ): ?>
<?php foreach( $message_array as $value ): ?>
<article>
    <div class="info">
        <h2><?php echo $value['view_name']; ?></h2>
        <time><?php echo date('投稿時間 H:i', strtotime($value['post_date'])); ?>
              <?php echo date('編集時間 H:i', strtotime($value['modified'])); ?>
        </time>
    </div>
    <p><?php echo nl2br($value['message']); ?></p>
</article>
<?php endforeach; ?>
<?php endif; ?>
</section>
</body>
</html>