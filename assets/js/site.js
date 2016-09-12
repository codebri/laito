$(function () {

    // Sintax Highlighting
    hljs.configure({
        languages: ['bash', 'http', 'php', 'json', 'html', 'css', 'javascript']
    });
    $('pre code').each(function(i, block) {
        hljs.highlightBlock(block);
    });
});