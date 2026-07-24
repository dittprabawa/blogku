<?php

it('redirects the homepage to the public blog', function () {
    $response = $this->get('/');

    $response->assertRedirect('/blog');
});
