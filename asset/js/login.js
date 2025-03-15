function showForm(formID){
    document.querySelectorAll(".form_box").forEach(form => form.classList.remove("active"));
    document.getElementById(formID).classList.add("active");
}