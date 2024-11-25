/* Description: Dealer tag for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */
let dealer_tagData = new Set()
let dealer_selected = new Set()
const DEALER_DROPDOWN_SIZE = 1

function dealer_fetchTags() {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        let tags = this.responseText.split('<br>')
        tags = tags.filter((tag) => tag !== '')
        
        dealer_tagData = new Set(tags)
        dealer_filter('')  // start with all tags
    }
    xhttp.open("GET", "./requestHandler.php?var1=dealer_tags", true);   
    xhttp.send();
}
function fetchTagsNew(newTag) {
    const xhttp = new XMLHttpRequest();
    xhttp.onload = function() {
        let tags = this.responseText.split('<br>')
        tags = tags.filter((tag) => tag !== '')

        dealer_tagData = new Set(tags)
        dealer_filter(newTag)  // start with th newTag
    }
    xhttp.open("GET", "./requestHandler.php?var1=dealer_tags", true);   
    xhttp.send();
}

// filter & show tags depending on search bar
function dealer_filter(prefix) {
    // filter matching prefix
    let filteredTags = Array.from(dealer_tagData).filter((tag) => {
        return tag.toLowerCase().startsWith(prefix.toLowerCase())
    })

    // sort to show selected tags first
    filteredTags.sort((a, b) => {
        return dealer_selected.has(b) - dealer_selected.has(a)
    })

    filteredTags = filteredTags.slice(0, Math.min(DEALER_DROPDOWN_SIZE, filteredTags.length))
    //alert("hi"+filteredTags);
    const resultDiv = document.getElementById('dealer_result')

    if (filteredTags.length === 0) {
        resultDiv.innerHTML = `
        <button value="${prefix}" onclick="addTag(this.value)">
            Add New Tag
        </button>`
        return;
    }

    const optionElements = filteredTags.map((tag) => {
        let checkedOption = ''
        if (dealer_selected.has(tag)) {
            checkedOption = 'checked'
        }

        return `<div>
            <input
                type="checkbox"
                id="checkbox-${tag}"
                value="${tag}"
                ${checkedOption}
                onclick="dealer_toggleTagSelection(this.value)"
            >
            <label 
                id="label-${tag}"
                for="checkbox-${tag}"
            >
                ${tag}
            </label>
        </div>`
    })

    resultDiv.innerHTML = optionElements.join(' ')
}

function dealer_toggleTagSelection (tag) {
    const tagCheckbox = document.getElementById('checkbox-' + tag)
    if (dealer_selected.has(tag)) {
        dealer_selected.delete(tag)
        tagCheckbox.checked = false;
    } else {
        dealer_selected.add(tag)
        tagCheckbox.checked = true;
    }
    
    const selectedDiv = document.getElementById('dealer_selected')

    const selectedElements = Array.from(dealer_selected).map((tag) => {
        return `
        <button 
            id="selected-${tag}"
            value="${tag}"
            onclick="dealer_toggleTagSelection(this.value)"
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
