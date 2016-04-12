$(document).ready(function() {
    // Read auth key
    var auth_key = $("meta[auth-key]").attr("auth-key");

    // Append sign in form to header
    var signinForm = $(document.createElement("div")).hide().attr("id", "sign-in-form").html("<form action=\"signin.php\" method=\"POST\">" +
        "<input type=\"text\" name=\"email\" autocomplete=\"off\" placeholder=\"Email\" />" +
        "<input type=\"password\" name=\"password\" placeholder=\"Password\" />" +
        "<button type=\"submit\">Sign In</button>" +
        "<p>Not a member? <a href=\"create.php\">Create an account!</a></p>" +
    "</form>");
    $("#user-links").append(signinForm);

    // Toggle sign in form when sign in link is clicked
    $("#sign-in").on("click", function(e) {
        signinForm.fadeToggle("fast", function() {
            $("#sign-in-form input:first-child").focus();
        });
        e.preventDefault();
    });

    // Handle sign in form submission
    $("#sign-in-form").on("submit", "form", function(e) {
        var signData = { auth_key: auth_key, no_redirect: true };
        $.ajax({
            type: "post",
            url: "signin.php",
            data: $(this).serialize() + "&" + $.param(signData),
            success: function() { location.reload(); }
        });
        e.preventDefault();
    });

    // Hide sign in form when somewhere else is clicked
    $(document).click(function(event) {
        if(!$(event.target).closest("#user-links").length) {
            if($("#sign-in-form").is(":visible")) {
                $("#sign-in-form").fadeOut("fast");
            }
        }
    });

    // Handle sign out link click
    $("#sign-out").on("click", function(e) {
        $.ajax({
            type: "post",
            url: "signout.php",
            data: { auth_key: auth_key, no_redirect: true },
            success: function() { location.reload(); }
        });
        e.preventDefault();
    });
});