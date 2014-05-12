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
		if ($(this).prop('checked')) {
			$("#answer_mc, #answer_fb, #answer_cm").hide();
			$("#answer_tf").show();
		}
	});

	$("#question_type_mc").click(function () {
		if ($(this).prop('checked')) {
			$("#answer_fb, #answer_tf, #answer_cm").hide();
			$("#answer_mc").show();
		}
	});

	$("#question_type_fb").click(function () {
		if ($(this).prop('checked')) {
			$("#answer_mc, #answer_tf, #answer_cm").hide();
			$("#answer_fb").show();
		}
	});

	$("#question_type_cm").click(function () {
		if ($(this).prop('checked')) {
			$("#answer_mc, #answer_tf, #answer_fb").hide();
			$("#answer_cm").show();
		}
	});

	$(document).on('click', 'button', function () {
		var name = $(this).attr("name");

		if (name == "delete[]") {
			console.log("deleting!");
			if ($(".answer_mc").length > 1) {
				$(this).parent().remove();
			} else if ($(".answer_cm").length > 1) {
				// column match entries
				$(this).parent().remove();
			}
		} else if (name == "add_answer") {
			$("#answer_mc").append("<fieldset class='answer_mc'><label for='answer'>Answer</label><input type='text' name='answer[]' /><br/><label for='answer_correct'>Correct Answer?</label><input type='checkbox' name='answer_correct[]' /> Correct<br/><label for='answer_explanation'>Explanation</label><input type='text' name='answer_explanation[]' /><br/><button type='button' name='delete[]'>X</button></fieldset>");
		}  else if (name == "add_answer_cm") {
			// figure out the highest count
			var regex = /\[(\d+)\]\[\]/g;
			var cur = 0;

			$(".answer_cm input[name^='answer_cm['").each(function (i, el) {
				var name = el.name;
				var matches = regex.exec(name);

				if (matches) {
					if (parseInt(matches[1]) > cur) {
						cur = parseInt(matches[1]);
					}
				}
			});

			cur++;

			$("#answer_cm").append("<span class='answer_cm'><input type='text' name='answer_cm[" + cur + "][]' value=''><input type='text' name='answer_cm[" + cur + "][]' value='' /><button type='button' name='delete[]'>X</button></span><br/>");
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
