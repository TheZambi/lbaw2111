function addEventListeners() {
    let changeButtons = document.querySelectorAll("input.change-status-submit");
    for(changeButton of changeButtons) {
        changeButton.addEventListener("click", changeStatus);
    }

    let showArrivedCheck = document.querySelector("input#showArrived");
    showArrivedCheck.addEventListener("change", toggleArrived);
}

function changeStatus(event) {
    event.preventDefault();
    let purchase_id = this.getAttribute("data-id");
    let purchase_status_select = document.querySelector("select#status_" + purchase_id);

    let purchase_status;

    if(purchase_status_select != null)
        purchase_status = document.querySelector("select#status_" + purchase_id).value; 

    if(purchase_status != "Processing" && purchase_status != "Sent" && purchase_status != "Arrived") {
        return;
    }

    let url = "/management/managePurchases/" + purchase_id + "/status";
    let data = {'status' : purchase_status};

    sendAjaxRequest("PATCH", url, data, function() {

        if(this.status == 200) {
            let responseJson = JSON.parse(this.responseText);
            let status = responseJson['status'];

            let badgeEntry = document.querySelector("span#status-purchase-" + purchase_id);
            let badgeModal = document.querySelector("span#status-modal-" + purchase_id);

            badgeEntry.innerHTML = status;
            badgeModal.innerHTML = status;

            let badgeColor = status == "Processing" ? "info" : (status == "Sent" ? "primary" : "success");

            badgeEntry.className = "badge bg-" + badgeColor + " purchase-status-badge";
            badgeModal.className = "badge bg-" + badgeColor + " d-flex flex-column justify-content-center";
        }
    });
}

function toggleArrived() {
    let url = "/management/purchases?arrived=";

    let spinner = document.querySelector("div#manage_purchases_spinner");
    if(spinner.classList.contains("d-none")) {
        spinner.classList.remove("d-none");
        spinner.classList.add("d-block");
    }

    if(this.checked) {
        url += "true";
    } else {
        url += "false";
    }
    sendAjaxRequest('get', url, null, updatePurchaseList);
}

function updatePurchaseList() {
    if (this.status === 200) {
        let parser = new DOMParser();
        let new_list = parser.parseFromString(this.response, 'text/html').body.childNodes[0];
        let list = document.querySelector("ul#managePurchasesList");
        let spinner = document.querySelector("div#manage_purchases_spinner");
        list.replaceWith(new_list);
        if(spinner.classList.contains("d-block")) {
            spinner.classList.remove("d-block");
            spinner.classList.add("d-none");
        }
    }
}

addEventListeners();