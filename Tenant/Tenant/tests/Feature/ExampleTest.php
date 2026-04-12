<?php

it('boots the application container', function () {
    expect(app())->not()->toBeNull();
});
