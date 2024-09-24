jQuery(document).ready(function ($) {
    if ($('.wpfm-browse').length > 0) {
        $('.wpfm-browse').on('click', function () {
            $(this).closest('div').find('input[type=file]').trigger('click');
        });
    }

    $("#pass1").val('').keyup(check_pass_strength);
    $("#pass2").val('').keyup(check_pass_strength);
    $("#pass-strength-result").show();

    $(document).on('click', '.wpfm-eye', function () {
        var target = $(this), eye = target.data('eye'), close_eye = target.data('close_eye'), input_type = target.parent().find('input').attr('type');
        if( input_type === 'text') {
            target.parent().find('input').attr('type', 'password');
            target.attr('src', eye);
        }else{
            target.attr('src', close_eye);
            target.parent().find('input').attr('type', 'text');
        }
    });
});

const loadFile = function(event) {
    var image = document.getElementById('preview-img');
    image.src = URL.createObjectURL(event.target.files[0]);
    image.setAttribute('style', 'display:block;object-fit:cover;object-position:center;');
};

function check_pass_strength() {
    var pass1 = jQuery("#pass1").val(),
        pass2 = jQuery("#pass2").val(),
        strength;

    if (typeof pass2 === undefined) {
        pass2 = pass1;
    }

    var pwsL10n = {
        empty: "Strength indicator",
        short: "Very weak",
        bad: "Weak",
        good: "Medium",
        strong: "Strong",
        mismatch: "Mismatch"
    };

    jQuery("#pass-strength-result").removeClass('short bad good strong');
    if (!pass1) {
        jQuery("#pass-strength-result").html(pwsL10n.empty);
        return;
    }

    strength = wp.passwordStrength.meter(pass1, wp.passwordStrength.userInputBlacklist(), pass2);

    switch (strength) {
        case 2:
            jQuery("#pass-strength-result").addClass('bad').html(pwsL10n.bad);
            break;
        case 3:
            jQuery("#pass-strength-result").addClass('good').html(pwsL10n.good);
            break;
        case 4:
            jQuery("#pass-strength-result").addClass('strong').html(pwsL10n.strong);
            break;
        case 5:
            jQuery("#pass-strength-result").addClass('short').html(pwsL10n.mismatch);
            break;
        default:
            jQuery("#pass-strength-result").addClass('short').html(pwsL10n['short']);
    }
}
