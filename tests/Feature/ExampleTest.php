<?php

it('redirects home to login', function () {
    $this->get('/')->assertRedirect(route('login'));
});
