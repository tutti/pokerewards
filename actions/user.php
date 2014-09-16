<?php

if ($user) {
    echo "Currently logged in as ".$user->username;
} else {
    echo "Not logged in.";
}