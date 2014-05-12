/**
DURING FILL OUT	
- when question_type_tf is checked, hide #answer_normal and show #answer_true_false
- when question_type_mc or question_type_fb are selected, hide #answer_true_false, show #answer_normal
- #add_answer.click, add a new answer group of fields
- class delete_answer clicked, remove the corresponding answer field

ON SUBMIT
- if question_type is NOT tf, then require at least one answer with #answer_correct checked
- if question_type == fb, the #question value must contain at least one <__> to represent the blanks
- question must not be blank
*/
$(document).ready(function () {
	/*
	// check the setting of the question_type box.  it might be set when we load an existing question, so be sure to show/hide at this point
	if ($("#question_type_tf").prop('checked')) {
		$("#answer_mc, #answer_fb").hide();
		$("#answer_tf").show();
	} else if ($("#question_type_mc").prop('checked')) {
		$("#answer_fb, #answer_tf").hide();
		$("#answer_mc").show();
	} else if ($("#question_type_fb").prop('checked')) {
		$("#answer_fb, #answer_tf").hide();
		$("#answer_mc").show();
	}
	*/

	// We call taggingJS init on all "#tag" divs
	$("#question_cats").tagging({
		"no-spacebar": true,
		"no-duplicate": true,
		"no-duplicate-callback": window.alert,
		"no-duplicate-text": "Duplicate tags",
		"type-zone-class": "type-zone",
		"tag-box-class": "tagging",
		"forbidden-chars": [",", ".", "_", "?"],
		"edit-on-delete": false
	});

	// show/hide the relevant answer blocks depending on what question type is selected
	$("#question_type_tf").click(function () {
		if ($(this).is(':checked')) {
			$("#answer_mc, #answer_fb").hide();
			$("#answer_tf").show();
		}
	});

	$("#question_type_mc").click(function () {
		if ($(this).is(':checked')) {
			$("#answer_fb, #answer_tf").hide();
			$("#answer_mc").show();
		}
	});

	$("#question_type_fb").click(function () {
		if ($(this).is(':checked')) {
			$("#answer_mc, #answer_tf").hide();
			$("#answer_fb").show();
		}
	});

	$(document).on('click', 'button', function () {
		var name = $(this).attr("name");

		if (name == "delete[]") {
			console.log($(".answer_mc").length);

			if ($(".answer_mc").length > 1) {
				$(this).parent().remove();
			}
		} else if (name == "add_answer") {
			$("#answer_mc").append("<fieldset class='answer_mc'><label for='answer'>Answer</label><input type='text' name='answer[]' /><br/><label for='answer_correct'>Correct Answer?</label><input type='checkbox' name='answer_correct[]' /> Correct<br/><label for='answer_explanation'>Explanation</label><input type='text' name='answer_explanation[]' /><br/><button type='button' name='delete[]'>X</button></fieldset>");
		} 
	}).on('keyup', '#question', function () {
		if ($("#question_type_fb").prop('checked')) {
			setFBFields();
		}
	}).on('change', '#question_type_fb', function () {
		setFBFields();
	});

	function setFBFields() {
		var q = $("#question").val();

		var c = q.match(/___/g);

		var add = "";

		if (c && c.length) {
			var html = "<input type='text' name='answer_fb[]' />";

			for (var x = 0; x < c.length; x++) {
				// make sure, check to see if a corresponding answer input already exists
				add += html;
			}
		}

		$("#answer_fb").html(add);
	}

	$("#quiz").validate({
		rules: {
			question: {
				required: true
			},
			"answer_fb[]": {
				required: true
			}	
		},
		submitHandler: function (form) {
			// grab the multiple choice correct answers and place into an array in hidden form field
			var correct = new Array();

			$("input[name='answer_correct[]']").each(function (i, val) {
				if ($(this).prop('checked')) {
					correct.push(i);
				}	
			});

			$("#correct_answer").val(correct.join(","));

			// 
			form.submit();
		}
	});
});		
