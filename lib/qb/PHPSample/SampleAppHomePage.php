<?php
  require_once("./CSS Styles/StyleElements.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<h3>IPP PHP Sample App</h3>
<script type="text/javascript" src="https://appcenter.intuit.com/Content/IA/intuit.ipp.anywhere.js"></script>

</head>
<body>

<a href="index.php?connectWithIntuitOpenId">Go Back</a>&nbsp;&nbsp;&nbsp;

<?php
 # Add a link to allow the user to logout. The link makes a JavaScript call to intuit.ipp.anywhere.logout()
 echo '<a href="javascript:void(0)" onclick="return intuit.ipp.anywhere.logout(function () { window.location.href = \'index.php\'; });">Sign Out</a>&nbsp;&nbsp;&nbsp;';
?>
			
<a target="_blank" href="ReadMe.htm">Read Me</a><br />

<button class="myButton" style="margin-top:30px;margin-bottom:30px" title="Create an employee in QBO" onclick='createEmployee()'>Create Employee </button> <br />


<button class="myButton" style="margin-bottom:30px" title="Get the list of accounts in QBO" onclick='getAccounts()'>Get QBO Accounts </button> <br />

<button class="myButton" style="margin-bottom:30px" title="Create a General Journal entry in QBO" onclick='createGJE()'>Create Journal Entry </button> <br />

<button class="myButton" style="margin-bottom:30px" title="View QuikBooks Trial Balance Report" onclick='showTrialBalanceReport()'>Trial Balance Report </button> <br />

<script>
function createEmployee(){
window.location.href = "EmployeeCreate.php";
}

function getAccounts(){
window.location.href = "AccountsFindAll.php";
}

function createGJE(){
window.location.href = "CreateGJE.php";
}

function showTrialBalanceReport(){
window.location.href = "TrailBalanceReport.php";
}

</script>


</body>
</html>
