<?php

namespace iutnc\nrv\action;

class Preferences extends Action {

    protected function executeGet(): string
    {
        $res = "<h1>Préférences</h1><br>";
        $res .= "<ul>";
        foreach ($_SESSION['preferences'] as $preference) {
            $res .= "<li>" . $preference . "</li>";
        }
        $res .= "</ul>";
        return $res;
    }

    protected function executePost(): string
    {
        $preference = $_POST['preference'];
        if (!in_array($preference, $_SESSION['preferences'])) {
            $_SESSION['preferences'][] = $preference;
        }
        return $this->executeGet();
    }
}