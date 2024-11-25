/* Description: Process for User-Interface, Author: Ramesh Singh, Copyright © 2024 PASA */
let analysisReport

function fetchList() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "./requestHandler.php?var1=list", true);   
    xhr.onload = function() {
        //console.log("xhr.responseText:", xhr.responseText);
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data && Object.keys(data).length !== 0) {
                    console.log("JSON data:", data);
                    populateTable(data);
                } else {
                    console.log("Empty JSON received");
                }
            } catch (e) {
                console.error("Invalid JSON data", e);
            }
        } else {
            console.error('Error:' + xhr.status); // Handle errors
        }
        //
    };
    xhr.onerror = function() { console.error('Network error'); };
    xhr.send();
}

function populateTable(data){
    // Initialize counters
    let softwareIssues = 0;
    let hardwareIssues = 0;
    let deviceIssues = 0;
    let othersIssues = 0;

    // Count issues while populating table
    data.forEach(user => {
        if (user.result.includes('Software issue')) {
            softwareIssues++;
        }
        if (user.result.includes('Hardware issue')) {
            hardwareIssues++;
        }
    });

    // Initialize the issue table
    const issueTable = document.getElementById('issueTable');
    issueTable.innerHTML = ''; // Clear any existing content
    
    // Add heading text
    const heading = document.createElement('h3');
    heading.textContent = 'List of analyzed claims';
    heading.style.marginBottom = '20px';
    issueTable.appendChild(heading);
    
    // Create responsive table wrapper
    const tableWrapper = document.createElement('div');
    tableWrapper.className = 'table-responsive';
    
    // Create table with styling
    const table = document.createElement('table');
    table.className = 'modern-table';
    
    // Add CSS styles
    const styles = document.createElement('style');
    styles.textContent = `
        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .modern-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 14px;
        }
        .modern-table th {
            background: #f4f6f8;
            padding: 12px 15px;
            text-align: left;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #ddd;
        }
        .modern-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .modern-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .modern-table a {
            color: #3498db;
            text-decoration: none;
            margin-top: 5px;
            display: inline-block;
        }
        .modern-table a:hover {
            text-decoration: underline;
        }
    `;
    document.head.appendChild(styles);
    
    // Create table structure
    const tableHeader = document.createElement('thead');
    const tableBody = document.createElement('tbody');
    
    // Create and populate header row
    let headerRow = document.createElement('tr');
    // Date, User, VIN, Description, Model-Year, Analysis-Result, Status 
    let dt = document.createElement('th');
    dt.textContent = "Date";
    headerRow.appendChild(dt);
    let des = document.createElement('th');
    des.textContent = "Description";
    headerRow.appendChild(des);
    let vin = document.createElement('th');
    vin.textContent = "VIN";
    headerRow.appendChild(vin);
    let model = document.createElement('th');
    model.textContent = "Model-Year";
    headerRow.appendChild(model);
    let dealer = document.createElement('th');
    dealer.textContent = "Dealer-Number";
    headerRow.appendChild(dealer);
    let result = document.createElement('th');
    result.textContent = "Result";
    headerRow.appendChild(result);
    let sts = document.createElement('th');
    sts.textContent = "Status";
    headerRow.appendChild(sts);                                        
    tableHeader.appendChild(headerRow);
    
    // Populate table body with enhanced styling
    data.forEach(user => {
        const row = document.createElement('tr');
        const idCell = document.createElement('td');
        idCell.innerText = user.date;
        row.appendChild(idCell);
        const nameCell = document.createElement('td');
        nameCell.innerText = user.desc;
        row.appendChild(nameCell);
        const vinCell = document.createElement('td');
        vinCell.innerText = user.vin.slice(0, 9) + "-" + user.vin.slice(9);
        row.appendChild(vinCell);
        const modelCell = document.createElement('td');
        modelCell.innerText = user.model;
        row.appendChild(modelCell);
        const dealerCell = document.createElement('td');
        dealerCell.innerText = user.dealer_number;
        row.appendChild(dealerCell);
        const resultCell = document.createElement('td');
        resultCell.innerHTML = user.result;
        row.appendChild(resultCell);
        const emailCell = document.createElement('td');
        emailCell.innerHTML = user.status + 
        "<br><a href=\"claim.php?itemid=" + user.itemid + "\">Detail</a>";
        row.appendChild(emailCell);
        tableBody.appendChild(row);
    });
    
    // Assemble the table
    table.appendChild(tableHeader);
    table.appendChild(tableBody);
    tableWrapper.appendChild(table);
    issueTable.appendChild(tableWrapper);
    
    document.getElementById('info').innerHTML += "<br><b>Similar result:</b><br>";
    document.getElementById('info').innerHTML += `Total Software issues = ${softwareIssues}, Hardware issues = ${hardwareIssues}, Device issue = ${deviceIssues}, Others issue = ${othersIssues}`;
    document.getElementById('info').innerHTML += "<br><br><br><div style=\"text-align: center\"><a href=\"#\" class=\"new-claim-button\" onclick=\"exportTableToCSV(event)\">Export-List</a></div>";
}

function fetchDetail() {
    let params = new URLSearchParams(window.location.search);
    let param1 = params.get('itemid');
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "./requestHandler.php?itemdet="+param1, true);   
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('detailContent').innerHTML = xhr.responseText;
        } else {
            console.error('Error:' + xhr.status); // Handle errors
        }
    };
    xhr.onerror = function() { console.error('Network error'); };
    xhr.send();
}

function fetchItemRecord() {
    const urlParams = new URLSearchParams(window.location.search);
    const itemId = urlParams.get('itemid');
    if (itemId) {
        document.getElementById('fileUploadForm').style.display = 'none';
        //document.getElementById('info').innerHTML = 'Processing your request...';
        const responseDiv = document.getElementById('response');

        const xhr = new XMLHttpRequest();
        xhr.open("GET", `./requestHandler.php?itemid=${itemId}`, true); 
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Decode the content if it's a JSON string
                let decodedContent = xhr.responseText;
                try {
                    decodedContent = JSON.parse(xhr.responseText);
                } catch (e) {
                    // If parsing fails, it's not a JSON string, so we keep the original content
                }
                // Format the content into paragraphs
                            // Store the formatted content in analysisReport
            analysisReport = formatContentToParagraphs(decodedContent);
                responseDiv.innerHTML = formatContentToParagraphs(decodedContent);
                responseDiv.innerHTML += `
                <div class="button-container">
                    <a href="claim.php" class="new-claim-button">New-Claim</a>
                    <a href="#" class="new-claim-button" onclick="downloadContent(event)">Download</a>
                </div>`;
                /*responseDiv.innerHTML += "<br><br><br><a href=\"claim.php\" class=\"new-claim-button\">New-Claim</a>";
                responseDiv.innerHTML += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\" class=\"new-claim-button\">Download</a><br><br><br>";
                */
            } else {
                console.error('Error:' + xhr.status); // Handle errors
            }
        };
        xhr.onerror = function() { console.error('Network error'); };
        xhr.send();
    } else {
        fetchTags();
        dealer_fetchTags();
    }
} 

function downloadContent(event) {
    event.preventDefault();
    
    // Use analysisReport instead of getting content from responseDiv
    const content = analysisReport || 'No content available';
    
    // Create a temporary div to parse the HTML content
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;
    
    // Extract and format the content
    const claimId = tempDiv.querySelector('h3')?.textContent.trim() || 'N/A';
    const dealer = tempDiv.querySelector('label[for="dealer"]')?.textContent.trim() || 'N/A';
    const vin = tempDiv.querySelector('label[for="vin"]')?.textContent.trim() || 'N/A';
    const issueType = tempDiv.querySelector('label[for="issue"]')?.nextSibling?.textContent.trim() || 'N/A';
    const dateTime = tempDiv.querySelector('label[for="date"]')?.textContent.trim() || 'N/A';
    const desc = tempDiv.querySelector('label[for="desc"]')?.nextSibling?.textContent.trim() || 'N/A';
    const model = tempDiv.querySelector('label[for="model"]')?.textContent.trim() || 'N/A';
    const detail = tempDiv.querySelector('p[style="display: none"]')?.textContent.trim() || 'N/A';

    // Format the text content with proper spacing and sections
    const formattedContent = 
`=== ${claimId} ===

BASIC INFORMATION
----------------
${dealer}   ${vin}
Issue-type: ${issueType} ${dateTime}
Description: ${desc}
${model}

ANALYSIS RESULTS
--------------
CMU Display: ${tempDiv.querySelector('.m7g-red')?.textContent.trim() || 'N/A'}
Remote Tuner: ${tempDiv.querySelector('.m7g-green')?.textContent.trim() || 'N/A'}
Amplifier: ${tempDiv.querySelector('.m7g-green:last-child')?.textContent.trim() || 'N/A'}

Current Errors:
${Array.from(tempDiv.querySelectorAll('p')).filter(p => p.textContent.includes('0x')).map(p => p.textContent).join('\n')}

${detail}

=== End of Report ===`;

    // Create a blob with the formatted content
    const blob = new Blob([formattedContent], { 
        type: 'text/plain;charset=utf-8'
    });
    
    // Create and trigger download
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    const date = new Date().toISOString().slice(0,10);
    link.download = `claim-report-${date}.txt`;
    
    document.body.appendChild(link);
    link.click();
    
    // Clean up
    document.body.removeChild(link);
    window.URL.revokeObjectURL(link.href);
} 

function postClaim() {

    if (!validateDealerNumber()) {
        return false;
    }
    if (!validateIssueType()) {
        return false;
    }
    if (!validateDateTime()) {
        return false;
    }
    const fileInput = document.getElementById('file');
    if (!validateFileType(fileInput)) {
        return false;
    }
    var form = document.getElementById("fileUploadForm");
    var formData = new FormData(form);
    const setArray = Array.from(selectedTags);
    const setJson = JSON.stringify(setArray);
    formData.append('setData', setJson);
    formData.append('dealer_number', Array.from(dealer_selected)[0]);
    //console.log('dealer_selected:', Array.from(dealer_selected)[0]);

    // Get the value of opCode
    const opCode = document.getElementById('opCode').value;
    console.log('Current opCode:', opCode);
    // Ignore certain form inputs based on opCode
    if (opCode === 'newVin') {
        formData.delete('upfile');
    }
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "requestHandler.php", true);
    //xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function () { //alert(this.responseText);
        const responseDiv = document.getElementById('response');
        const infoDiv = document.getElementById('info');
        const errorDiv = document.getElementById('error');
        responseDiv.style.display = 'block'; 
        if (xhr.status === 200) {   // success
            console.log(this.responseText);
            const response = JSON.parse(this.responseText);
            if (response.success) {
                errorDiv.innerHTML = "";
                // Decode the content if it's a JSON string
                let decodedContent = response.content;
                try {
                    decodedContent = JSON.parse(response.content);
                } catch (e) {
                    // If parsing fails, it's not a JSON string, so we keep the original content
                }
                // Format the content into paragraphs
                responseDiv.innerHTML = formatContentToParagraphs(decodedContent);
                analysisReport = formatContentToParagraphs(decodedContent);
                infoDiv.innerHTML = `${response.info}`;
                //infoDiv.innerHTML += "<br><br><br><a href=\"claim.php\" class=\"new-claim-button\">New-Claim</a><br><br><br>";
                infoDiv.innerHTML += "<br><br><br><a href=\"claim.php\" class=\"new-claim-button\">New-Claim</a>";
                infoDiv.innerHTML += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"#\" class=\"new-claim-button\" onclick=\"downloadContent(event)\">Download</a><br><br><br>";
            } else {
                infoDiv.innerHTML = "";
                errorDiv.style="color: red;"
                errorDiv.innerHTML = `<br>${response.error}<br><br>`;
                //content = `${response.content}`;
                if (response.content === "vin-mismatch") { 
                    document.getElementById('vin').value = "";
                    document.getElementById('vin').focus();
                    document.getElementById('opCode').value = 'newVin';
                }
                else if (response.content === "decode-error") { 
                    document.getElementById('file').value = '';
                    document.getElementById('opCode').value = 'newClaim';
                }
            }
        } else {                    // failure
            responseDiv.innerHTML = "Error " + xhr.status + ": " + xhr.statusText;
        }
        document.getElementById('response').style.display = "block";
        document.getElementById('loader').style.display = "none";
    };
    xhr.onerror = function() { console.error('Network error'); };

    xhr.send(formData); // form data (file)

    document.getElementById('response').style.display = "none";
    document.getElementById('loader').style.display = "block";
}


// Helper function to format the content into paragraphs
function formatContentToParagraphs(content) {
    if (typeof content === 'object' && content !== null) {
        let formattedContent = '';
        const { dealer_number, tag, itemid, desc, date, time, vin, model, version, result, detail } = content;
        const versionParts = version ? version.split(';') : [];
        formattedContent = `
            <label for="heading"><h3>Claim# ${itemid}</h3></label><br>
            <div class="m7g-row">
                <div class="m7g-col m5 l4">
                    <label for="dealer"><b>Dealer#: </b>${dealer_number || 'N/A'}</label>
                </div>
                <div class="m7g-col m7 l8">
                <label for="vin"><b>VIN: </b>${vin || 'N/A'}</label>
                </div>
            </div><br>
            <div class="m7g-row">
                <div class="m7g-col m5 l4">
                    <label for="issue"><b>Issue-type:</b><br></label> ${tag}
                </div>
                <div class="m7g-col m7 l8">
                    <label for="date"><b>Issue-date & time:</b><br>${(!date || date === '0000-00-00') ? 'N/A' : date}</label>
                    <label for="time"><b></b>${(!time || time === '00:00:00') ? 'N/A' : time}</label>
                </div>
            </div><br>
            <label for="desc"><b>Description:</b><br></label> ${desc}<br><br>            
            <div class="m7g-row">
                <div class="m7g-col m7 l8">
                    <label for="heading"><h3>Analysis Information:</h3></label>
                </div>
                <div class="m7g-col m5 l4 m7g-right-align">
                    <br><a href=\"#\" class=\"new-claim-button\" onclick=\"openDetailWindow('${itemid}')\">Details</a>
                </div>
            </div><br>
            <label for="model"><b>Model, Year:</b><br>${model || 'N/A'}</label><br><br>
            <b>CMU Display:</b>
            <div class="m7g-cell-row m7g-border">
                <div class="m7g-container m7g-red m7g-cell">
                <p>${versionParts[0]}</p>
                <p><h3>Update the software</h3></p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Current)</p>
                <p>0x23EF</p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Past)</p>
                <p>0x23EF,0x23EF,0x23EF</p>
                </div>
            </div><br>
            <b>Remote Tuner:</b>
            <div class="m7g-cell-row m7g-border">
                <div class="m7g-container m7g-green m7g-cell">
                <p>${versionParts[1]}</p>
                <p><h3>No failure found</h3></p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Current)</p>
                <p>0x23EF</p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Past)</p>
                <p>0x23EF,0x23EF,0x23EF</p>
                </div>
            </div><br>
            <b>Amplifier:</b>
            <div class="m7g-cell-row m7g-border">
                <div class="m7g-container m7g-green m7g-cell">
                <p>${versionParts[2]}</p>
                <p><h3>No failure found</h3></p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Current)</p>
                <p>0x23EF</p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Past)</p>
                <p>0x23EF,0x23EF,0x23EF</p>
                </div>
            </div><br>
            <b>Others:</b>
            <div class="m7g-cell-row m7g-border">
                <div class="m7g-container m7g-orange m7g-cell">
                <p><h3>Check on the details page</h3></p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Current)</p>
                <p>0x23EF</p>
                </div>
                <div class="m7g-container m7g-cell">
                <p>Error(Past)</p>
                <p>0x23EF,0x23EF,0x23EF</p>
                </div>
            </div><br>
            <p style="display: none">${detail}</p>
            `;
            
        /*
        <b>Version:</b><br>${version || 'N/A'}<br><br>
        <b>Result:</b><br>${result || 'N/A'}<br><br>
        <b>Log-detail:</b><br>${detail || 'N/A'}
        */
        return formattedContent;
    } else if (typeof content === 'string') {
        return content.split('\n').map(para => `<p>${para}</p>`).join('');
    } else {
        return `<p>${content}</p>`;
    }
}

// Helper function to format individual values
function formatValue(value) {
    if (typeof value === 'object' && value !== null) {
        return formatContentToParagraphs(value);
    } else {
        return value;
    }
}

// Add this new function at the bottom of your file
function openDetailWindow(itemid) {
    const width = 800;
    const height = 600;
    const left = (window.screen.width - width) / 2;
    const top = (window.screen.height - height) / 2;
    const newWindow = window.open('', 'DetailWindow', 
        `width=${width},
         height=${height},
         left=${left},
         top=${top},
         scrollbars=yes,
         resizable=yes`
    );
    
    // Write initial HTML with loading state
    newWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Log Details</title>
            <style>
                body { 
                    padding: 20px; 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6;
                }
                .log-section {
                    margin-bottom: 20px;
                    padding: 10px;
                    background-color: #f5f5f5;
                    border-radius: 4px;
                    text-align: left;
                }
                .log-title {
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 5px;
                    text-align: left;
                }
                .log-content {
                    white-space: pre-wrap;
                    font-family: monospace;
                    text-align: left;
                }
                .loading {
                    padding: 20px;
                    text-align: left;
                }
                #detailContent {
                    text-align: left;
                }
            </style>
        </head>
        <body>
            <div id="detailContent" class="loading">Loading...</div>
        </body>
        </html>
    `);
    
    // Fetch the content from server
    const xhr = new XMLHttpRequest();
    xhr.open("GET", `./requestHandler.php?itemdet=${itemid}`, true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            newWindow.document.getElementById('detailContent').innerHTML = xhr.responseText;
        } else {
            newWindow.document.getElementById('detailContent').innerHTML = 
                'Error loading content. Please try again.';
        }
    };
    
    xhr.onerror = function() {
        newWindow.document.getElementById('detailContent').innerHTML = 
            'Network error occurred. Please try again.';
    };
    
    xhr.send();
    newWindow.document.close();
}

// Add this new function to handle CSV export
function exportTableToCSV(event) {
    event.preventDefault();
    
    // Get the table element
    const table = document.getElementById('tableBody');
    const rows = table.getElementsByTagName('tr');
    
    // Prepare CSV content with headers
    let csvContent = 'Date,Description,VIN,Model-Year,Dealer-Number,Result,Status\n';
    
    // Convert table data to CSV
    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        let rowData = [];
        
        for (let cell of cells) {
            // Get only the text content, removing any HTML tags
            let cellText = cell.innerText.replace(/[\n\r]+/g, ' ').trim();
            // Escape quotes and wrap content in quotes if it contains commas
            cellText = cellText.replace(/"/g, '""');
            if (cellText.includes(',')) {
                cellText = `"${cellText}"`;
            }
            rowData.push(cellText);
        }
        
        csvContent += rowData.join(',') + '\n';
    }
    
    // Create a blob and trigger download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    // Set filename with current date
    const date = new Date().toISOString().slice(0, 10);
    link.setAttribute('href', url);
    link.setAttribute('download', `claims-list-${date}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
