$(function () {
    $('body').on('click', "button[type='submit']", function (e) {
        let $this = $(this);
        setTimeout(function () {
            $this.addClass("disabled").prop("disabled", true);
            $this.html(
                "<i class='fa-solid fa-arrows-rotate fa-spin fs-3'></i>&nbsp;Working ..."
            );
            // Re-enable the button after 15 seconds
            setTimeout(function () {
                $this.removeClass("disabled").prop("disabled", false);
                $this.html("Submit");
            }, 15000);
        }, 100);

    });
});

