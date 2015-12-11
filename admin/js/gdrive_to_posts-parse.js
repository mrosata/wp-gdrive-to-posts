jQuery(function($) {
  "use strict";
  alert('cool!');

  $.ajax({
    url: gdriveToPostsAll.ajaxURL,
    type: 'post',
    data: {
      nonce: gdriveToPostsAll.nonce,
      action: 'gdrive_to_posts_parse_through_template'
    },
    dataType: 'json',
    complete: function(resp) {
      console.log(resp);
    }
  })

});