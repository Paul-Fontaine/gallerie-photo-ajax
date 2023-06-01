'use strict';

let login = 'cir2';
let currentPhotoId = 1;

function htmlElementFromString(string) {
    let template = document.createElement('template');
    string = string.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = string;
    return template.content.firstChild;
}



/**************PHOTOS*******************************************************************/


ajaxRequest('GET', 'php/request.php/photos', displayPhotos);
ajaxRequest('GET', 'php/request.php/photos/'+currentPhotoId, displayPhoto);

function thumbnail(value, src){
    let string =
    "<div class='col-xs-2 col-md-2'>" +
        "<a href='#'>" +
            `<img value='${value}' src='${src}' class='img-thumbnail'>` +
        "</a>" +
    "</div>";
    return htmlElementFromString(string);
}

function addClickOnThumnails(){
    $('section#thumbnails div a img').each( (index, thumbnail) => {
        console.log(thumbnail);
        console.log($(thumbnail));
        $(thumbnail).on('click', (event) => {
            let id = $(event.target).attr('value');
            ajaxRequest('GET', 'php/request.php/photos/'+id, displayPhoto);
        });
    });
}

function displayPhotos(photos){
    photos = JSON.parse(photos)
    for (const photo of photos){
        $("#thumbnails").append(thumbnail(photo.id, photo.src));
    }
    addClickOnThumnails();

}

function displayPhoto(photo) {
    photo = JSON.parse(photo);
    let eltString =
        "<div class='card col-xs-12 col-md-12'>" +
            "<div class='card-body'>" +
                `<h4>${photo.title}</h4>` +
            `<img src='${photo.src}' class='img-thumbnail'>` +
        "</div>";
    $('section#photo').html(eltString);
    $('section#photo').attr('photoid', photo.id);

    currentPhotoId = photo.id;
    ajaxRequest('GET', 'php/request.php/commentaires/?photoid='+photo.id, displayComments);
}



/**************COMMENTAIRES*******************************************************************/

$(document).ready(() => { // jsp pq il faut mettre ça mais sur w3schools ils font comme ça
    $("form").submit( (event) => {
        event.preventDefault();
        let text = $('#comment-input').val();

        ajaxRequest('POST', 'php/request.php/commentaires/',
            () => {
                ajaxRequest('GET', 'php/request.php/commentaires/?photoid='+currentPhotoId, displayComments);
            },
            'userlogin='+login + '&photoid='+currentPhotoId + '&text='+text);

        $('#comment-input').val('');
    });
});

function displayComments(comments){
    comments = JSON.parse(comments);
    $('section#comments').html('');
    for (const comment of comments){
        $('section#comments').append(div_comment(comment));
    }
    addClickOnEditComment();
    addClickOnDelete();
}

function div_comment(comment){
    let string =
        "<div class='card'>" +
            "<div class='card-body'>" +
                `${comment.userlogin} : ${comment.comment}` +
                "<div class='btn-group float-right' role='group'>" +
                    `<button type='button' class='btn btn-light float-right mod' value='${comment.id}'>` +
                        "<i class='fa fa-edit'></i>" +
                    "</button>" +
                    `<button type='button' class='btn btn-light float-right del' value='${comment.id}'>` +
                        "<i class='fa fa-trash'></i>" +
                    "</button>" +
            "</div>" +
        "</div>"
    return htmlElementFromString(string);
}

function addClickOnEditComment(){
    for (const editButton of $('#comments button.mod')){
        editButton.onclick = (event) => {
           let id =  $(event.target).closest('button.mod').attr('value'); //id du commentaire

           ajaxRequest(
               'PUT',
               'php/request.php/commentaires/'+id,
               () => {
                    ajaxRequest('GET', 'php/request.php/commentaires/?photoid='+currentPhotoId, displayComments); // une fois le commentaire modifié, on recharge les commentaires
               },
               'userlogin=' + login + '&comment=' + prompt('commentaire modifié :') // data pour modifier le commentaire
           );
        };
    }
}

function addClickOnDelete(){
    for (const delButton of $('#comments button.del')){
        delButton.onclick = (event) => {
            let id =  $(event.target).closest('button.del').attr('value'); //id du commentaire

            ajaxRequest(
                'DELETE',
                'php/request.php/commentaires/'+id +'?userlogin='+login,
                () => {
                    ajaxRequest('GET', 'php/request.php/commentaires/?photoid='+currentPhotoId, displayComments); // une fois le commentaire modifié, on recharge les commentaires
                },
                'userlogin=' + login
            );
        }
    }
}