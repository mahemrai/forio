/**
 * admin.js
 * Contains javascript for admin section.
 * @author Mahendra Rai
 */
$(document).ready(function() {
    var rect, file_to_crop;

    //initialize tooltips
    $('[data-toggle="tooltip"]').tooltip({
        delay: {
            "show": 100,
            "hide": 100
        }
    });

    /**
     * Load modal when button for adding image is clicked.
     */
    $('button.load-image-form').click(function(e) {
        e.preventDefault();
        $('#image-form').modal('show');
    });
    
    /**
     * Load modal when button for creating new project is clicked.
     */
    $('.load-project-form').click(function(e) {
        e.preventDefault();
        $('div#typeahead-keywords').empty();
        $('input[type=text]').val('');
        $('textarea').val('');
        $('#project-form').modal('show');
    });

    /**
     * Load modal when button for creating new experience is clicked.
     */
    $('.load-experience-form').click(function(e) {
        e.preventDefault();
        $('input[type=text]').val('');
        $('#experience-form').modal('show');
    });

    /**
     * Load modal when button for creating new education is clicked.
     */
    $('.load-education-form').click(function(e) {
        e.preventDefault();
        $('input[type=text]').val('');
        $('#education-form').modal('show');
    });

    /**
     * Load modal for editing personal info.
     */
    $('.edit-info').click(function(e) {
        e.preventDefault();
        $.get('/admin/info')
            .done(function(response) {
                $('input[type=text].firstname').val(response.data.firstname);
                $('input[type=text].lastname').val(response.data.lastname);
                $('input[type=text].title').val(response.data.title);
                $('input[type=text].location').val(response.data.location);
                $('textarea.bio').val(response.data.bio);
                $('input[type=text].clients').val(response.data.clients);
                $('#info-form').modal('show');
        });
    });

    $('.save-info').click(function(e) {
        e.preventDefault();
        var json = {
            firstname : $('input[type=text].firstname').val(),
            lastname  : $('input[type=text].lastname').val(),
            email     : $('input[type=text].email').val(),
            title     : $('input[type=text].user-title').val(),
            location  : $('input[type=text].location').val(),
            bio       : $('textarea.bio').val(),
            clients   : $('input[type=text].clients').val()
        };

        console.log(json);

        $.post('/admin/info/save', {data: json})
            .done(function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
        });
    });

    $('.profile-pic').click(function(e) {
        e.preventDefault();
        $('#profile-pic-form').modal('show');
    });

    $('input:file.profile').change(function() {
        $.ajax({
            url         : '/admin/profile-pic/upload',
            type        : 'post',
            data        : new FormData($('form.profile')[0]),
            contentType : false,
            processData : false
        });
    })

    $('.save-profile-pic').click(function(e) {
        e.preventDefault();
        var json = {
            file : $('input[type=file]').val().split('/').pop().split('\\').pop(),
        };

        $.post('/admin/profile-pic/save', {data: json})
            .done(function(data) {
                if (data.success) {
                    location.reload(true);
                } else {
                    alert('Could not save profile picture. Try again.');
                }
            });
    });
    
    /**
     * Reset form and remove previously selected image when modal
     * is closed.
     */
    $('#image-form').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('.image-cropper > img').cropper('destroy');
        $('.preview').attr('src', '/public/image/site/noimage.jpg');
    });
    
    /**
     * Upload image asynchronously using ajax request when a file
     * is selected.
     */
    $('input:file.project').change(function() {
        $.ajax({
            url: '/admin/image/upload',
            type: 'post',
            data: new FormData($('form')[0]),
            contentType: false,
            processData: false
        })
        .done(function(data) {
            preview_image = data.temp_img_path;
            $('.preview').attr('src', data.temp_img_path);
        });
    });
    
    /**
     * Save image data to database, crop image and create thumbnail by
     * sending an ajax request.
     */
    $('.btn-process').click(function(e) {
        e.preventDefault();
        var json = {
            file    : $('input[type=file]').val().split('/').pop().split('\\').pop(),
            title   : $('input[type=text].image-title').val(),
            desc    : $('textarea.image-desc').val(),
            rect    : rect,
            project : $(this).data('project')
        };
        
        $.post('/admin/image/process', {data: json})
            .done(function(data) {
                if (data.success) {
                    location.reload(true);
                } else {
                    alert('Could not complete image upload process. Try again.');
                }
        });
    });
    
    /**
     * Assign image url to src attribute of img tag in the image viewer modal
     * and load the modal.
     */
    $('.image-view').click(function() {
        var image = $(this).data('image');
        $('div.image-viewer > img').attr('src', image);
        $('#imageview').modal({'show':true});
    });
    
    /**
     * Send GET request to the server and retrieve image info before
     * loading modal containing form for editing the selected image.
     */
    $('.image-edit').click(function() {
        var id = $(this).data('id');
        
        //retrieve data and assign values to form fields
        $.get('/admin/image/' + id)
            .done(function(response) {
                $('input[type=hidden].id').val(id);
                $('input[type=text].title').val(response.data.title);
                $('textarea.desc').val(response.data.description);
                $('#edit-form').modal({'show': true});
        });
    });

    $('.image-delete').click(function() {
        $.ajax({
            url: '/admin/image/' + $(this).data('id') + '/delete',
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload(true);
                }
            }
        })
    });
    
    /**
     * Update image info by sending a POST request.
     */
    $('.save-image').click(function(e) {
        e.preventDefault();
        var json = {
            title : $('input[type=text].title').val(),
            desc  : $('textarea.desc').val(),
            id    : $('input[type=hidden].id').val()
        };

        console.log(json);
        
        $.post('/admin/image/save', {data: json})
            .done(function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert('Could not update the image. Try again.');
                }
        });
    });
    
    /**
     * Create new project by sending a POST request.
     */
    $('.save-project').click(function(e) {
        e.preventDefault();
        var json = {
            title    : $('input[type=text].title').val(),
            desc     : $('textarea.desc').val(),
            type     : $('input[type=text].type').val(),
            for      : $('input[type=text].for').val(),
            date     : $('input[type=text].date').val(),
            keywords : $('input[type=text].keywords').val(),
            id       : $('input[type=hidden].project-id').val()
        }

        console.log(json);

        $.post('/admin/projects/save', {data: json})
            .done(function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert('Could not create new project. Try again.');
                }
        });
    });

    /**
     * Send GET request to server to fetch project information
     * and populate the form for editing.
     */
    $('.edit-project').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.get('/admin/project/' + id + '/edit')
            .done(function(response) {
                console.log(response);
                $('input[type=text].title').val(response.data.title);
                $('textarea.desc').val(response.data.description);
                $('input[type=text].type').val(response.data.type);
                $('input[type=text].for').val(response.data.for);
                $('input[type=text].date').val(response.data.project_date);
                //loop through keywords and add tags to div
                $.each(response.data.keywords, function(key, value){
                    $('div#typeahead-keywords').html(
                        '<span class="label label-primary">'+value.name+'<a class="keyword-delete" href="#" data-keyword="'+value.id+'" data-project="'+response.data.id+'"><i class="fa fa-times"></i></a></span>'
                    );
                });
                $('input[type=hidden].project-id').val(response.data.id);
                $('#project-form').modal('show');
        });
    });

    /**
     * Send DELETE request to delete project from the database.
     */
    $('.delete-project').click(function(e) {
        e.preventDefault();
        $.ajax({
            url  : '/admin/project/' + $(this).data('id') + '/delete',
            type : 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
            }
        });
    });

    /**
     * Send POST request to add new experience.
     */
    $('.save-experience').click(function(e) {
        e.preventDefault();
        var json = {
            title        : $('input[type=text].title').val(),
            organisation : $('input[type=text].organisation').val(),
            location     : $('input[type=text].location').val(),
            startdate    : $('input[type=text].start-date').val(),
            enddate      : $('input[type=text].end-date').val(),
            id           : $('input[type=hidden].experience-id').val()
        };

        $.post('/admin/experiences/save', {data : json})
            .done(function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
        });
    });

    /**
     * Send GET request to retrieve experience details for editing.
     */
    $('.edit-experience').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.get('/admin/experience/' + id + '/edit')
            .done(function(response) {
                $('input[type=text].title').val(response.data.title);
                $('input[type=text].organisation').val(response.data.organisation);
                $('input[type=text].location').val(response.data.location);
                $('input[type=text].start-date').val(response.data.start_date);
                $('input[type=text].end-date').val(response.data.end_date);
                $('input[type=hidden].experience-id').val(response.data.id);
                $('#experience-form').modal('show');
        });
    });

    /**
     * Send DELETE request to delete experience.
     */
    $('.delete-experience').click(function(e) {
        e.preventDefault();
        $.ajax({
            url     : '/admin/experience/' + $(this).data('id') + '/delete',
            type    : 'DELETE',
            success : function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
            }
        });
    });

    /**
     * Send POST request to add new education.
     */
    $('.save-education').click(function(e) {
        e.preventDefault();
        var json = {
            course    : $('input[type=text].course').val(),
            school    : $('input[type=text].school').val(),
            location  : $('input[type=text].location').val(),
            startyear : $('input[type=text].start-year').val(),
            endyear   : $('input[type=text].end-year').val(),
            id        : $('input[type=hidden].education-id').val()
        };

        $.post('/admin/educations/save', {data : json})
            .done(function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
            })
    });

    /**
     * Send GET request to retrieve education details for setting.
     */
    $('.edit-education').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');

        $.get('/admin/education/' + id + '/edit')
            .done(function(response) {
                $('input[type=text].course').val(response.data.course);
                $('input[type=text].school').val(response.data.school);
                $('input[type=text].location').val(response.data.location);
                $('input[type=text].start-year').val(response.data.start_year);
                $('input[type=text].end-year').val(response.data.end_year);
                $('input[type=hidden].education-id').val(response.data.id);
                $('#education-form').modal('show');
        });
    });

    /**
     * Send DELETE request to delete education.
     */
    $('.delete-education').click(function(e) {
        e.preventDefault();
        $.ajax({
            url : '/admin/education/' + $(this).data('id') + '/delete',
            type : 'DELETE',
            success : function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
            }
        });
    });

    /**
     * Send DELETE request to server to delete relationship between the selected
     * keyword and project.
     */
    $('div#typeahead-keywords').on('click', 'a.keyword-delete', function(e) {
        e.preventDefault();
        var keyword = $(this).data('keyword');
        var project = $(this).data('project');

        $.ajax({
            url: '/admin/keyword/'+project+'/'+keyword,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload(true);
                } else {
                    alert(response.message);
                }
            }
        });
    });
    
    /**
     * Create bloodhound object for automcomplete
     */
    var keywords = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: '/admin/keywords/%QUERY'
    });
    
    keywords.initialize();
    
    /**
     * Typeahead configuration
     */
    $('#typeahead').tagsinput({
       typeaheadjs: {
           name       : 'keywords',
           displayKey : 'name',
           valueKey   : 'name',
           source     : keywords.ttAdapter()
       }
    });
});