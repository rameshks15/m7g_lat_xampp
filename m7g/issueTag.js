/* Description: Issuetag for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */
let tagData = new Set()
let selectedTags = new Set()
const DROPDOWN_SIZE = 3

function fetchTags() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        let tags = this.responseText.split('<br>')
        tags = tags.filter((tag) => tag !== '')
        
        tagData = new Set(tags)
        filter('')  // start with all tags
    }
    xhttp.open("GET", "./requestHandler.php?var1=tags", true);   
    xhttp.send();
}
function fetchTagsNew(newTag) {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        let tags = this.responseText.split('<br>')
        tags = tags.filter((tag) => tag !== '')

        tagData = new Set(tags)
        filter(newTag)  // start with th newTag
    }
    xhttp.open("GET", "./requestHandler.php?var1=tags", true);   
    xhttp.send();
}

// filter & show tags depending on search bar
function filter(prefix) {
    // filter matching prefix
    let filteredTags = Array.from(tagData).filter((tag) => {
        return tag.toLowerCase().startsWith(prefix.toLowerCase())
    })

    // sort to show selected tags first
    filteredTags.sort((a, b) => {
        return selectedTags.has(b) - selectedTags.has(a)
    })

    filteredTags = filteredTags.slice(0, Math.min(DROPDOWN_SIZE, filteredTags.length))
    //alert("hi"+filteredTags);
    const resultDiv = document.getElementById('result')

    if (filteredTags.length === 0) {
        resultDiv.innerHTML = `
        <button value="${prefix}" onclick="addTag(this.value)">
            Add New Tag
        </button>`
        return;
    }

    const optionElements = filteredTags.map((tag) => {
        let checkedOption = ''
        if (selectedTags.has(tag)) {
            checkedOption = 'checked'
        }

        //return `<div>
        return `
            <input
                type="checkbox"
                id="checkbox-${tag}"
                value="${tag}"
                ${checkedOption}
                onclick="toggleTagSelection(this.value)"
            >
            <label 
                id="label-${tag}"
                for="checkbox-${tag}"
            >
                ${tag}
            </label>
        ` + `&nbsp;`
        //</div>`
    })

    resultDiv.innerHTML = optionElements.join(' ')
}

function toggleTagSelection (tag) {
    const tagCheckbox = document.getElementById('checkbox-' + tag)
    if (selectedTags.has(tag)) {
        selectedTags.delete(tag)
        tagCheckbox.checked = false;
    } else {
        selectedTags.add(tag)
        tagCheckbox.checked = true;
    }
    
    const selectedDiv = document.getElementById('selected')

    const selectedElements = Array.from(selectedTags).map((tag) => {
        return `
        <button 
            id="selected-${tag}"
            value="${tag}"
            onclick="toggleTagSelection(this.value)"
        >
            ${tag} (X)
        </button>`
    })

    selectedDiv.innerHTML = selectedElements.join('')
}

function addTag(tagName) {
    console.log(tagName)
    let ajax = new XMLHttpRequest();
    ajax.open('POST', 'requestHandler.php?opCode=addTag', true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    ajax.onload = function() {
        //alert(this.response);
        fetchTagsNew(tagName);
    };
    let data = JSON.stringify({opCode:"addTag",newTag:tagName})
    ajax.send(data);
}
