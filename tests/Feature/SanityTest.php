<?php

it('boots the testbench application', function () {
    expect(app()->bound('translator'))->toBeTrue();
});
