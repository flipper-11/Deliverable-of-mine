<!DOCTYPE html>
<html lang = "ja">

<head>
    <meta charset = "utf-8">
    <title>Mission5</title>
</head>
<body>
<h1>昨日の夜ご飯何食べた？</h1>
他に聞くことも思いつかなかったので、昨日の夜ご飯が何だったかでも教えてください。
<hr>

<?php
 //【下準備１】DBに接続しておく(m4-1)
 $dsn = 'mysql:dbname="データベース名";host=localhost';
 $user = 'ユーザー名';
 $password = 'パスワード';
 $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

 //【下準備２】DB内にテーブルを作成する（テーブルが存在しない場合のみ実行する）(m4-2)
 //同じDBに接続してさえいれば、この部分は他のファイルを作って実行しても問題ないはず
 $sql = "CREATE TABLE IF NOT EXISTS Mission_5"
 . " ( "
 . " id INT AUTO_INCREMENT PRIMARY KEY," //カラム"id"には、自動で番号が振られる
 . " name char(32)," //カラム"name"には、半角英数で32文字まで入る(最大値は255バイト)
 . " com TEXT," //カラム"com"には、長めの文章も入る(最大65,535バイトまで)
 . " date TIMESTAMP," //カラム"date"には、日付+時刻が入る
 . " pass char(32)" //カラム"pass"には、半角英数で32文字まで入る　//本のP.805を参照した
 . ");";
 $stmt = $pdo -> query($sql);


 if(!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["pass"]) && !empty($_POST["sub1"])){
     $name = $_POST["name"];
     $com = $_POST["comment"];
     $pass = $_POST["pass"];
     $sub1 = $_POST["sub1"];


     if(!empty($_POST["num_for_edit"])){
         $num_for_edit = $_POST["num_for_edit"];
         //UPDATE文を使ってデータレコードの内容を編集(m4-7)
         $id = $num_for_edit; //変更したい投稿番号を指定
         //4-7では$nameと$comも指定しているけど、フォームの中身をそのまま受け取ればいいので、すでに定義済み
         $sql = 'UPDATE Mission_5 SET name=:name,com=:com WHERE id=:id';
         $stmt = $pdo -> prepare($sql);
         $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
         $stmt -> bindParam(':com', $com, PDO::PARAM_STR);
         $stmt -> bindParam(':id', $id, PDO::PARAM_STR);
         $stmt -> execute();
     }else{
         $date = date("Y/m/d H:i:s");
         //「Mission_5」という名のテーブル上に登録していく(m4-5)
         $sql = $pdo -> prepare("INSERT INTO Mission_5 (name, com, date, pass) VALUES(:name, :com, :date, :pass)");
         $sql -> bindParam(':name', $name, PDO::PARAM_STR);
         $sql -> bindParam(':com', $com, PDO::PARAM_STR);
         $sql -> bindParam(':date', $date, PDO::PARAM_STR);
         $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
         $sql -> execute();
         header("Location: ./5-1.php"); //画面更新による二重投稿の防止(投稿送信後に新ページへ飛ばし、フォームを空に)
     }

 }elseif(!empty($_POST["edinum"]) && !empty($_POST["edipass"]) && !empty($_POST["sub2"])){
     $edinum = $_POST["edinum"];
     $edipass = $_POST["edipass"];
     $sub2 = $_POST["sub2"];

     //SELECT文で[edinum]番の投稿データを引っぱってくる→パスワード確認→フォームに表示(m4-6応用)
     $sql = 'SELECT * FROM Mission_5 WHERE id=:id';
     $stmt = $pdo -> prepare($sql);
     $stmt -> bindParam(':id', $edinum, PDO::PARAM_INT);
     $stmt -> execute(); //SQL実行
     $results = $stmt -> fetchAll();
     
     if(!empty($results)){
        foreach($results as $row){
            if($edipass == $row['pass']){  //edipassと投稿のパスワードが一致したら
                $edinum_truepass = $row['id'];
                $name_truepass = $row['name'];
                $com_truepass = $row['com'];
            }else{
                $edinum_truepass = "";  //「echo $edinum_truepass」で空文字を書き込ませる。投稿があったら新規扱いになるはず。
                $name_truepass = "";
                $com_truepass = "";
                $display = "正しいパスワードを入力してください";
            }
        }
     }else{
         $display = "指定された番号の投稿は存在しません";
     }

 }elseif(!empty($_POST["delnum"]) && !empty($_POST["delpass"]) && !empty($_POST["sub3"])){
     $delnum = $_POST["delnum"];
     $delpass = $_POST["delpass"];
     $sub3 = $_POST["sub3"];
     
     //delpassと投稿のpassが一致するか確認するため、SELECT文で[delnum]番の投稿データを引っぱってくる(m4-6応用)
     $sql = 'SELECT * FROM Mission_5 WHERE id=:id';
     $stmt = $pdo -> prepare($sql);
     $stmt -> bindParam(':id', $delnum, PDO::PARAM_INT);
     $stmt -> execute(); //SQL実行
     $results = $stmt -> fetchAll();

     if(!empty($results)){
         foreach($results as $row){
             if($delpass == $row['pass']){
                 //DELETE文を使ってデータレコードを削除(m4-8)
                 $sql = 'delete from Mission_5 where id=:id';
                 $stmt = $pdo -> prepare($sql);
                 $stmt -> bindParam(':id', $delnum, PDO::PARAM_INT);
                 $stmt -> execute();
                 $display = "投稿を削除しました";
             }else{
                 $display = "正しいパスワードが入力されていません";
             }
         }
     }else{
         $display = "指定された番号の投稿は存在しません";
     }
 }
?>

<!--新規&編集投稿受け取り用-->
<form  action = ""  method = "post">
名前：<input  type = "text"  name = "name" 
        value = "<?php if(!empty($_POST["edinum"]) && !empty($_POST["sub2"]) && ($display != "指定された番号の投稿は存在しません")){
                   echo $name_truepass;
                } ?>"><br>
コメント：<input  type = "text"  name = "comment" style = "width:350px"
                 value = "<?php if(!empty($_POST["edinum"]) && !empty($_POST["sub2"]) && ($display != "指定された番号の投稿は存在しません")){
                    echo $com_truepass;
                } ?>"><br>
パスワード：<input  type = "text"  name = "pass">
<input  type = "hidden"  name = "num_for_edit"  
 value = "<?php if(!empty($_POST["edinum"]) && !empty($_POST["sub2"]) && ($display != "指定された番号の投稿は存在しません")){
                    echo $edinum_truepass;
                }?>">　      <!--編集番号の受け取り-->
<input  type = "submit"  name = "sub1">
</form><hr>

<!--編集用-->
<form action="" method="post">
編集対象番号：<input type="number" name="edinum" min=1 max=50>
パスワード：<input type="text" name="edipass">
<input  type = "submit"  name = "sub2"  value = "編集">
</form><hr>

<!--削除用-->
<form action="" method="post">
削除対象番号：<input type="number" name="delnum" min=1 max=50>
パスワード：<input type="text" name="delpass">
<input  type = "submit"  name = "sub3"  value = "削除">
</form><hr>

<?php
 //「投稿を受け付けました」「投稿を削除しました」等メッセージの表示
 if(!empty($display)){
     echo $display."<hr>";
 }
?>

<h3>投稿</h3>
<?php
 //SELECT文を使ってデータを表示(m4-6)
 $sql = 'SELECT * FROM Mission_5';
 $stmt = $pdo -> query($sql);
 $results = $stmt -> fetchAll();
 foreach ($results as $row){
     echo $row['id'].'  ';
     echo $row['name'].'  ';
     echo $row['com'].'  ';
     echo $row['date'].'<br>';
 }
?>

</body>