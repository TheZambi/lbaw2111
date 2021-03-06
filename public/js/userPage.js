const acceptedTypes = ['image/png', 'image/jpg', 'image/jpeg'];

function addEventListeners() {
    let notificationButtons = document.querySelectorAll('.deleteNotificationButton');
    for(let notificationButton of notificationButtons)
    {
        notificationButton.addEventListener("click", seeNotification);
    }
}


function addImageEventListener(){
    let submitButton = document.getElementById("submitImage");
    submitButton.addEventListener("click",submitImage);
}

function isAcceptedImageType(imageType){
    for(let i = 0; i < acceptedTypes.length; i++){
        if(imageType == acceptedTypes[i])
            return true;
    }
    return false;
}

function previewImage(event){
    const reader = new FileReader();

    //Check if image exists
    if(event.target.files.length > 0){
        // Checks if image extension is valid
        if(isAcceptedImageType(event.target.files[0].type)){
            reader.readAsDataURL(event.target.files[0]);
            reader.onload = () => {
                const preview = document.getElementById('preview');
                preview.src = reader.result;
            };

        }
    }
    return;
}

function submitImage(){
    event.preventDefault();
    let img = document.getElementById("imageInput");
    //Verify if image exists
    if(img.files.length <= 0){
    
        return;
    }
    //Verify image extension
    if(!isAcceptedImageType(img.files[0].type)){
        return;
    }

    
    let ext = ((img.files[0].type).split("/"));
    if(ext.length != 2){
        return;
    }

    let image = img.files[0];


    let url = "/userProfile/editImage";

    let formData = new FormData();
    var file = image;
    formData.append("images[]", file, file.name);

    sendFileAjaxRequest('POST', url, formData, function () {
        if (this.status === 200) {
            let pfpHtml = document.getElementById("userPageProfilePic");
            let pfpHtmlSmall = document.getElementById("profilePic");
            //Change current URL
            url = URL.createObjectURL(image);
            pfpHtml.style.backgroundImage = 'url("' + url + '")';
            pfpHtmlSmall.style.backgroundImage = 'url("' + url + '")';

        }
        else if(this.status === 500){
        }
    });
    
    return;
}

function editUsernameForm(username, id){

    let doc = document.getElementById("userNameContent");

    let str = `<section id="userNameContent">
                    <form action="/userProfile/edit" method="post">

                        <textarea name="newUsername" class="w-100 h-50" required id="newUsername" rows=1 style="resize: none;" placeholder=` + username + `></textarea>
                        <div id="errorUsername" class="pb-2" style="color:red;font-size:15px;"></div>
                        
                        <button id="submitNewUsername" data-id='` + id + `' type="button" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Submit</button>
                        <button id="cancelNewUsername" data-id='` + id + `'  type="button" class="btn btn-danger"><i class="bi bi-x-circle-fill"></i> Cancel</button></td>

                    </form>
                </section>`

    let parser = new DOMParser();
    let add = parser.parseFromString(str, 'text/html').body.firstChild;

    doc.replaceWith(add);

    //can't add in the beggining since these buttons do not exist
    let submitButton = document.getElementById("submitNewUsername");
    submitButton.addEventListener("click",submitNewUsername);

    let cancelButton = document.getElementById("cancelNewUsername");
    cancelButton.addEventListener("click",cancelNewUsername);
}

function submitNewUsername(event){
    event.preventDefault();
    let doc = document.getElementById("userNameContent");
    let userId = this.getAttribute('data-id');

    let newUsername = document.getElementById("newUsername").value;

    if(newUsername == null || newUsername.length < 4){
        let err = document.getElementById("errorUsername");
        err.innerHTML = "Username should have at least 4 characters!"
        return;
    }

    let data = {'username': newUsername};

    let url = "/userProfile/editUsername";

    sendAjaxRequest('PATCH', url, data, function () {
        if (this.status === 200) {

            let str = `<section id="userNameContent">
                    ` + newUsername + `
                    <button type="button" class="btn" onclick=editUsernameForm('` + newUsername + `',` + userId + `)>
                        <i class="bi bi-pencil-square" ></i>
                    </button>
                </section>`

            let parser = new DOMParser();
            let add = parser.parseFromString(str, 'text/html').body.firstChild;

            doc.replaceWith(add);
        }
        if(this.status === 500){
            let err = document.getElementById("errorUsername");
            err.innerHTML = "Username already in use!"
        }
    });
}

function cancelNewUsername(event){
    event.preventDefault();

    let doc = document.getElementById("userNameContent");
    let userId = this.getAttribute('data-id');

    let username = document.getElementById("newUsername").getAttribute("placeholder");

    let str = `<section id="userNameContent">
                    ` + username + `
                    <button type="button" class="btn" onclick=editUsernameForm('` + username + `',` + userId + `)>
                        <i class="bi bi-pencil-square" ></i>
                    </button>
                </section>`

    let parser = new DOMParser();
    let add = parser.parseFromString(str, 'text/html').body.firstChild;

    doc.replaceWith(add);
        
}

function seeNotification(event) {
    event.preventDefault();
    let notificationId = this.getAttribute("data-id");

    let button = this;
    
    let url = "/notification/" + notificationId;

    let data = null;

    sendAjaxRequest('PATCH', url, data, function () {
        if (this.status === 200) {
            button.closest("li").remove();
        }});
}

function deleteAccount(id) {
    
    let url = "/userProfile/" + id;

    let data = null;

    sendAjaxRequest('DELETE', url, data, function () {
        if (this.status === 200) {
            window.location.replace('/');
        }});
}

function getAddressEditForm(type)
{
    if(type != "shipping" && type != "billing") 
        return;

    let url = "/userProfile/edit/getAddressForm/" + type;
     

    sendAjaxRequest('GET', url, null, function () {
        if (this.status === 200) {

            let doc;
            if(type == "shipping")
                doc = document.getElementById("userShipping");
            else 
                doc = document.getElementById("userBilling");
            let parser = new DOMParser();
            let add = parser.parseFromString(this.response, 'text/html').body.firstChild;

            doc.replaceWith(add);

            if(type == "shipping")
                document.querySelector("button#submitNewShippingAddress").addEventListener("click",submitNewShippingInfo);
            else 
                document.querySelector("button#submitNewBillingAddress").addEventListener("click",submitNewBillingInfo);

        }});
}

function getAddressInfo(type)
{
    let url = "/userProfile/edit/getAddressInfo/" + type;

    sendAjaxRequest('GET', url, null, function () {
        if (this.status === 200) {

            let doc;
            if(type == "shipping") {
                doc = document.getElementById("userShipping");
            } else {
                doc = document.getElementById("userBilling");
            }
            let parser = new DOMParser();
            let add = parser.parseFromString(this.response, 'text/html').body.firstChild;

            doc.replaceWith(add);
        }});    
}

function submitNewShippingInfo(event)
{
    event.preventDefault();
    let url = "/userProfile/editAddress/shipping";

    let newCountryDropdown = document.getElementById("newCountry");
    let newCountry = newCountryDropdown.value;

    let newCity = document.getElementById("newCity").value;

    let newStreet = document.getElementById("newStreet").value;

    let zip = document.getElementById("newZip").value;

    let data = {'newStreet': newStreet, "newCountry": newCountry, "newCity": newCity, "newZip": zip};

    sendAjaxRequest('PATCH', url, data, function () {

        if (this.status === 200) {
            let doc = document.getElementById("userShipping");
            let parser = new DOMParser();
            let add = parser.parseFromString(this.response, 'text/html').body.firstChild;

            doc.replaceWith(add);
        }});    
}

function submitNewBillingInfo(event)
{
    event.preventDefault();
    let url = "/userProfile/editAddress/billing";

    let newCountryDropdown = document.getElementById("newCountry");
    let newCountry = newCountryDropdown.value;

    let newCity = document.getElementById("newCity").value;

    let newStreet = document.getElementById("newStreet").value;

    let zip = document.getElementById("newZip").value;


    let data = {'newStreet': newStreet, "newCountry": newCountry, "newCity": newCity, "newZip": zip};

    sendAjaxRequest('PATCH', url, data, function () {

        if (this.status === 200) {
            let doc = document.getElementById("userBilling");
            let parser = new DOMParser();
            let add = parser.parseFromString(this.response, 'text/html').body.firstChild;

            doc.replaceWith(add);
        }});    
}



//
// Change email
//

function editEmailForm(email, id){

    let doc = document.getElementById("emailContent");

    let str = `<section id="emailContent">
                    <form action="/userProfile/edit" method="post">

                        <textarea name="newEmail" class="w-100 h-50" required id="newEmail" rows=1 style="resize: none;" placeholder=` + email + `></textarea>
                        <div id="errorEmail" class="pb-2" style="color:red;font-size:15px;"></div>
                        <button id="submitNewEmail" data-id='` + id + `' type="button" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Submit</button>
                        <button id="cancelNewEmail" data-id='` + id + `'  type="button" class="btn btn-danger"><i class="bi bi-x-circle-fill"></i> Cancel</button></td>

                    </form>
                </section>`

    let parser = new DOMParser();
    let add = parser.parseFromString(str, 'text/html').body.firstChild;

    doc.replaceWith(add);

    //can't add in the beggining since these buttons do not exist
    let submitButton = document.getElementById("submitNewEmail");
    submitButton.addEventListener("click",submitNewEmail);

    let cancelButton = document.getElementById("cancelNewEmail");
    cancelButton.addEventListener("click",cancelNewEmail);
}

function cancelNewEmail(event){
    event.preventDefault();

    let doc = document.getElementById("emailContent");
    let userId = this.getAttribute('data-id');

    let email = document.getElementById("newEmail").getAttribute("placeholder");

    let str = `<section id="emailContent">
                    ` + email + `
                    <button type="button" class="btn" onclick=editEmailForm('` + email + `',` + userId + `)>
                        <i class="bi bi-pencil-square" ></i>
                    </button>
                </section>`

    let parser = new DOMParser();
    let add = parser.parseFromString(str, 'text/html').body.firstChild;

    doc.replaceWith(add);
        
}

function validateEmail(email) {
    const re = /\S+@\S+\.\S+/;
    return re.test(String(email).toLowerCase());
}

function submitNewEmail(event){
    event.preventDefault();
    let doc = document.getElementById("emailContent");
    let userId = this.getAttribute('data-id');

    let newEmail = document.getElementById("newEmail").value;

    if(newEmail == null || !validateEmail(newEmail)){
        let err = document.getElementById("errorEmail");
        err.innerHTML = "Invalid Email Selected"
        return;
    }
    else{
        let err = document.getElementById("errorEmail");
        err.innerHTML = ""
    }

    let data = {'email': newEmail};

    let url = "/userProfile/editEmail";

    sendAjaxRequest('PATCH', url, data, function () {
        if (this.status === 200) {

            let str = `<section id="emailContent">
                    ` + newEmail + `
                    <button type="button" class="btn" onclick=editUsernameForm('` + newEmail + `',` + userId + `)>
                        <i class="bi bi-pencil-square" ></i>
                    </button>
                </section>`

            let parser = new DOMParser();
            let add = parser.parseFromString(str, 'text/html').body.firstChild;

            doc.replaceWith(add);
        }
        if(this.status === 500){
            let err = document.getElementById("errorEmail");
            err.innerHTML = "Email already in use!"
        }
    });
}


addEventListeners();
addImageEventListener();