<?php
# process feedback sent


session_start();
require_once("functions.php");

$caller = basename($_SERVER['HTTP_REFERER']);

if ($caller != 'feedback.html') {
  header("Location: /");
  exit;
}

# name, email, phone, message and if logged on antibot sum come in as a POST var
$userid = $_POST['userid'];
$name = $_POST['name'];
$email = $_POST['email'];
$state = $_POST['state'];
$tx = $_POST['tx'];
$easeOfUse = $_POST['easeOfUse'];
$valueForMoney = $_POST['valueForMoney'];
$wouldRecommend = $_POST['wouldRecommend'];
$comment = $_POST['comment'];


$query = "INSERT INTO feedback (userid, name, state, email, easeofuse, valueformoney, wouldrecommend, comments, received) VALUES ($userid, ?, ?, ?, $easeOfUse, $valueForMoney, $wouldRecommend, ?, NOW());";

$dbh = dbConnect();
$sth = $dbh->prepare($query);
$sth->bindParam(1, $name, PDO::PARAM_STR);
$sth->bindParam(2, $state, PDO::PARAM_STR);
$sth->bindParam(3, $email, PDO::PARAM_STR);
$sth->bindParam(4, $comment, PDO::PARAM_STR);
$sth->execute();

# send email to sender and a bcc to admin
$body = "Feedback from $name ($email)\n\nEase of Use: $easeOfUse\nValue For Money: $valueForMoney\nWould Recommend: $wouldRecommend\n\n$comment";
# send an email to admin
adminEmail("$name <$email>", "Feedback Message", $body);

# send web response back to submitter
echo "Thank you. Your feedback has been received.";

?>