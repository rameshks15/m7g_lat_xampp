<!-- Description: Analysis page, Author: Ramesh Singh, Copyright © 2024 PASA -->
<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/m7g/config.php');
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];
    header("Location: ../profile/login.php"); exit;
} //else { echo "SESSION[email]=".$_SESSION['email']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M7G - Analysis</title>
    <link rel="stylesheet" href="m7g-styles.css">
    <!--<link rel="stylesheet" href="w3style.css">-->
    <script src="claimProcessor.js"></script>
    <script src="issueTag.js"></script>
    <script src="dealerTag.js"></script>
    <style>
    /* Basic styling */
    </style>
</head>
<body>
    <header class="header">
        <div class="navbar">
            <a href="./index.php">Dashboard</a>
            <a href="#" class="active"><b>Analysis</b></a>
            <a href="../profile/index.php">Profile</a>
        </div>
    </header>

    <div class="container">
        <main class="content">

            <div id="loader">
                <img src="loading.gif" alt="Loading..." />
            </div>
            <div id="error" class="error-message"> </div>
            <div id="response">
                <form id="fileUploadForm" onsubmit="event.preventDefault(); postClaim();" enctype="multipart/form-data">
                    <input type="hidden" id="opCode" name="opCode" value="newClaim">
                    <label for="heading"><h3>New-Claim</h3></label>
                    <div class="m7g-row">
                    <div class="m7g-col m5 l4">
                        <label for="dealer"><b>Dealer-number:</b></label>
                        <div id="dealer_result"></div>
                        <input type="text" placeholder="Search..." id="dealer" onkeyup="dealer_filter(this.value)" style="width: 80%;">
                        <div id="dealer_selected" name="dealer_selected" style="display: none;"></div>
                        <div id="dealer_error" class="validation-error"></div>
                    </div>
                    <div class="m7g-col m7 l8">
                    <label for="issue"><b>Issue-type:</b></label>
                            <div id="result"></div>
                            <input type="text" placeholder="Search..." id="issue" onkeyup="filter(this.value)" style="width: 60%;">
                            <div id="selected" name="selected" style="display: none;"></div>
                            <div id="issue_error" class="validation-error"></div>
                    </div>
                    </div>
                    <label for="desc"><b>Description:</b></label>
                    <textarea id="desc" name="desc" required style="width: 75%;"></textarea><br>
                                        
                    <div class="m7g-row">
                        <div class="m7g-col m5 l4">
                        <label for="date"><b>Issue-date</b></label>
                            <div class="date-group">
                                <input type="date" name="date" id="date" required max="<?php echo date('Y-m-d'); ?>">
                                <label class="unknown-label">
                                    <input type="checkbox" id="date_unknown" name="date_unknown" onchange="toggleDateField()">
                                    Unknown
                                </label>
                            </div>
                            <div id="date_error" class="validation-error"></div>
                        </div>
                        <div class="m7g-col m7 l8">
                            <label for="time"><b>Issue-time</b></label>
                            <div class="date-group">
                                <input type="time" name="time" id="time" required>
                                <label class="unknown-label">
                                    <input type="checkbox" id="time_unknown" name="time_unknown" onchange="toggleTimeField()">
                                    Unknown
                                </label>
                            </div>
                            <div id="time_error" class="validation-error"></div>
                        </div>
                    </div><br>

                    <div class="m7g-row">
                    <div class="m7g-col m5 l4">
                        <label for="vin"><b>V I N</b></label><br>
                        <input type="text" name="vin" id="vin" required>
                    </div>
                    <div class="m7g-col m7 l8">
                    <label for="file"><b>Log-data:</b></label><br>
                        <input type="file" 
                                name="upfile" 
                                id="file" 
                                accept=".zip,.7z" 
                                required 
                                onchange="validateFileType(this)">
                        <div id="file_error" class="validation-error"></div>
                    </div>
                    </div>
                    <br><br>
                    <div style="text-align: center;">
                        <input type="submit" value="Analyze" class="submit-button" style="width: auto;">
                    </div>
                </form>
            </div>
            <div id="info"> </div>
        </main>
        <!--<aside class="sidebar">
            <h3>Sidebar</h3> 
        </aside>-->
    </div>
    <footer class="footer">
        <p>© 2024 Panasonic Automotive Systems</p>
    </footer>

    <script>
        window.addEventListener('DOMContentLoaded', fetchItemRecord);    
        document.getElementById('response').style.display = "block";
        document.getElementById('loader').style.display = "none";
      
        function validateIssueType() {
            const selectedIssueType = document.getElementById('selected').textContent;
             if (!selectedIssueType) { //alert("No issueType selected"); 
                 document.getElementById('issue_error').textContent = selectedIssueType + 'Please select an issue type';
                return false;
            } else { //alert("issueTypes="+selectedIssueType); 
                return true; 
            }
        }
        function validateDealerNumber() {
            const selectedDealerNumber = document.getElementById('dealer_selected').textContent;
             if (!selectedDealerNumber) { //alert("No issueType selected"); 
                 document.getElementById('dealer_error').textContent = selectedDealerNumber + 'Please select a dealer number';
                return false;
            } else { //alert("dealerNumber="+selectedDealerNumber); 
                return true; 
            }
        }

        function validateDateTime() {
            const dateField = document.getElementById('date');
            const timeField = document.getElementById('time');
            const dateUnknown = document.getElementById('date_unknown');
            const timeUnknown = document.getElementById('time_unknown');
            const dateError = document.getElementById('date_error');
            const timeError = document.getElementById('time_error');
            let isValid = true;
            // Date validation
            if (!dateUnknown.checked) {
                if (!dateField.value) {
                    dateError.textContent = 'Please select a date or check Unknown';
                    isValid = false;
                } else {
                    const selectedDate = new Date(dateField.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selectedDate > today) {
                        dateError.textContent = 'Date cannot be in the future';
                        isValid = false;
                    } else {
                        dateError.textContent = '';
                    }
                }
            }
            // Time validation
            if (!timeUnknown.checked) {
                if (!timeField.value) {
                    timeError.textContent = 'Please select a time or check Unknown';
                    isValid = false;
                } else {
                    timeError.textContent = '';
                }
            }
            return isValid;
        }

        function toggleDateField() {
            const dateField = document.getElementById('date');
            const unknownCheckbox = document.getElementById('date_unknown');
            const dateError = document.getElementById('date_error');           
            if (unknownCheckbox.checked) {
                dateField.value = '';
                dateField.disabled = true;
                dateField.required = false;
                dateError.textContent = '';
            } else {
                dateField.disabled = false;
                dateField.required = true;
            }
        }

        function toggleTimeField() {
            const timeField = document.getElementById('time');
            const unknownCheckbox = document.getElementById('time_unknown');
            const timeError = document.getElementById('time_error');          
            if (unknownCheckbox.checked) {
                timeField.value = '';
                timeField.disabled = true;
                timeField.required = false;
                timeError.textContent = '';
            } else {
                timeField.disabled = false;
                timeField.required = true;
            }
        }

        function validateFileType(input) {
            const file = input.files[0];
            const fileError = document.getElementById('file_error');
            const allowedExtensions = /(\.zip|\.7z)$/i;
            if (!allowedExtensions.exec(file.name)) {
                fileError.textContent = 'Please upload only .zip or .7z files';
                input.value = '';
                return false;
            } else {
                fileError.textContent = '';
                return true;
            }
        }

    </script>

</body>
</html>
