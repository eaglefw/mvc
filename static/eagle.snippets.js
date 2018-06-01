
if (!window.jQuery)
    console.error('jQuery must be present to make Eagle snippets work.');
else {

    $(document).on('click', '[data-process]', function(e) {

        e.preventDefault();

        var data = $(this).data(),
            queryString = '';

        (Object.keys(data)).forEach(function(key) {

            if(queryString === '' && countURLParameters() === 0)
                queryString += '?';
            else
                queryString += '&';

            queryString += key + '=' + encodeURIComponent(data[key]);

        });

        $.ajax({
            url: window.location + queryString
        })
        .done(function(response) {

            if(response.eagleProcess !== undefined) {

                var snippets = response.eagleProcess.snippets,
                    snippetKeys = Object.keys(response.eagleProcess.snippets);

                snippetKeys.forEach(function(snippetId) {

                    var snippetContent = snippets[snippetId],
                        snippetDom = $('#snippet-' + snippetId);

                    if(snippetDom.length > 0) {

                        snippetDom.html(snippetContent);
                    }
                });
            }
        });

    });

    /**
     * Number of GET parameters
     */

    function countURLParameters() {

        var count = 0;

        var queryString = window.location.search.slice(1);

        // if query string exists
        if (queryString) {

            // stuff after # is not part of query string, so get rid of it
            queryString = queryString.split('#')[0];

            // split our query string into its component parts
            var arr = queryString.split('&');

            for (var i = 0; i < arr.length; i++)
                count++;
        }

        return count;
    }
}