/*
* octavalidate - PHP Helper Script
* This script helps to display server-side validation errors on the
* frontend for the end user to see
* Make sure to include this script on the page that contains the form
*/

(function () {
    //global Styling
    if (!document.querySelector('style#octavalidate-global-style')) {
        const ovStyle = document.createElement("style");
        ovStyle.id = "octavalidate-global-style";
        ovStyle.innerHTML = `
    .octavalidate-inp-error:not([type="checkbox"], [type="file"], [type="radio"]){
    border-color: #e43f5a !important;
background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
background-repeat: no-repeat;
background-position: right calc(0.375em + 0.1875rem) center;
background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
} 
.octavalidate-inp-success:not([type="checkbox"], [type="file"], [type="radio"]){
    border-color: #4caf50 !important;
background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
background-repeat: no-repeat;
background-position: right calc(0.375em + 0.1875rem) center;
background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}
.octavalidate-txt-error{
    display:block;
    color : #d10745;
    font-size: 1rem;
    margin: 5px 0px 0px 0px;
}`;
        document.head.appendChild(ovStyle);
    }
}());

function removeErrors(form_id) {
    const formChildren = document.querySelector('#' + form_id).children;
    let ind = 0;
    if (formChildren) {
        while (ind < formChildren.length) {
            //p tag
            const pTag = formChildren[ind].querySelector('p');
            //input tag
            const inputTag = formChildren[ind].querySelector('input');
            //textarea
            const textTag = formChildren[ind].querySelector('textarea');
            if (pTag && pTag.classList.contains('octavalidate-txt-error')) {
                pTag.remove();
            }
            if (inputTag && inputTag.classList.contains('octavalidate-inp-error')) {
                inputTag.classList.remove('octavalidate-inp-error');
                inputTag.classList.add('octavalidate-inp-success');
            }
            if (textTag && textTag.classList.contains('octavalidate-inp-error')) {
                textTag.classList.remove('octavalidate-inp-error');
                textTag.classList.add('octavalidate-inp-success');
            }
            ind++;
        }
    }
}
function showErrors(errorsObj) {
    const isObject = (obj) => {
        return (Object.prototype.toString.call(obj) === '[object Object]');
    };
    const findElem = (id) => {
        return (document.querySelector('#' + id) !== null);
    };
    if (typeof errorsObj == "undefined" || !isObject(errorsObj) || Object.keys(errorsObj).length === 0)
        throw new Error(
            "A Valid Object must be passed as an argument to the [showErrors] function if you want to display server-side Form Errors.")
    //assign form id
    const form_id = Object.keys(errorsObj)[0];
    
    if (!findElem(form_id))
        throw new Error(`A form with this id [${form_id}] does not Exist`)

    removeErrors(form_id);

    //loop through error object
    Object.entries(errorsObj[form_id]).forEach(eo => {
        const inputName = (eo[0] !== undefined) ? eo[0] : null;
        const errorText = (eo[1] !== undefined) ? eo[1] : null;
        if (inputName && errorText) {
            //check if this error is for 2 form inputs
            if (inputName.match(/:/)) {
                inputName.split(':').forEach(inp => {
                    //loop through all form inputs
                    document.querySelectorAll('#' + form_id + ' [name]').forEach(ie => {
                        //ie -> input element from the form
                        //inp -> input element of form from error object
                        if (ie.name === inp) {
                            //remove success class
                            ie.classList.remove('octavalidate-inp-success');
                            //add error class
                            if (!ie.classList.contains('octavalidate-inp-error')) {
                                ie.classList.add("octavalidate-inp-error");
                            }
                            //create error text
                            const g = document.createElement("p");
                            //check if id exists on element
                            if (ie.id) {
                                g.setAttribute("id", "octavalidate_" + ie.id);
                            } else {
                                //use input name if id does not exist
                                g.setAttribute("id", "octavalidate_" + inp);
                            }
                            g.setAttribute("class", "octavalidate-txt-error");
                            g.innerText = errorText;
                            //insert error text after the input element
                            ie.after(g);
                            //maybe frontend inputElement.watch() to validate in frontend again
                            //Listen to change in input value, then remove the error
                            if (ie.addEventListener) {
                                ie.addEventListener("change", function(){
                                    if(this.value.trim() !== ""){
                                        this.classList.remove("octavalidate-inp-error");
                                        //if error text element exists
                                        if(g){
                                            g.remove()
                                        }
                                    }
                                }, { once: true });
                            } else if (elem.attachEvent) {
                                ie.attachEvent("change", function(){
                                    if(this.value.trim() !== ""){
                                        this.classList.remove("octavalidate-inp-error");
                                        //if error text element exists
                                        if(g){
                                            g.remove()
                                        }
                                    }
                                });
                            }
                        }
                    });
                })
            } else {
                //loop through all form inputs
                document.querySelectorAll('#' + form_id + ' [name]').forEach(ie => {
                    if (ie.name === inputName) {
                        //remove success class
                        ie.classList.remove('octavalidate-inp-success');
                        //add error class
                        if (!ie.classList.contains('octavalidate-inp-error')) {
                            ie.classList.add("octavalidate-inp-error");
                        }
                        //create error text
                        const g = document.createElement("p");
                        if (ie.id) {
                            g.setAttribute("id", "octavalidate_" + ie.id);
                        } else {
                            g.setAttribute("id", "octavalidate_" + inputName);
                        }
                        g.setAttribute("class", "octavalidate-txt-error");
                        g.innerText = errorText;
                        //insert after
                        ie.after(g);
                        //Listen to change in input value, then remove the error
                        if (ie.addEventListener) {
                            ie.addEventListener("change", function(){
                                if(this.value.trim() !== ""){
                                    this.classList.remove("octavalidate-inp-error");
                                    //if error text element exists
                                    if(g){
                                        g.remove()
                                    }
                                }
                            }, { once: true });
                        } else if (elem.attachEvent) {
                            ie.attachEvent("change", function(){
                                if(this.value.trim() !== ""){
                                    this.classList.remove("octavalidate-inp-error");
                                    //if error text element exists
                                    if(g){
                                        g.remove()
                                    }
                                }
                            });
                        }
                    }
                });
            }
        }
    });
}