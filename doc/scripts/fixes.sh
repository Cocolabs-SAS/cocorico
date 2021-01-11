#!/bin/bash
# Usage:
# [SCRIPTNAME].sh dry   - dry run
# [SCRIPTNAME].sh run   - execute

updates=(
    's/ Twig_Extension / Twig\\Extension\\AbstractExtension /g'
    's/ Twig_Extension;/ Twig\\Extension\\AbstractExtension;/g'
    's/ Twig_Extension_GlobalsInterface/ Twig\\Extension\\GlobalsInterface/g'
    's/{% spaceless %}/{% apply spaceless %}/g'
    's/{% endspaceless %}/{% endapply %}/g'
    's/Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route/Symfony\\Component\\Routing\\Annotation\\Route/g'
)


shift $((OPTIND -1))
subcommand=$1; shift


case "$subcommand" in
    debug)
        for update in "${updates[@]}"
        do
            echo "sed -n ${update}p"
        done
        ;;
    dry)
        for update in "${updates[@]}"
        do
            find ../../src/ -type f \( -name '*.php' -o -name '*.twig' \) -readable -writable -exec sed -n "${update}p" {} \;
        done
        ;;
    run)
        for update in "${updates[@]}"
        do
            find ../../src/ -type f \( -name '*.php' -o -name '*.twig' \) -readable -writable -exec sed -i "$update" {} \;
        done
        ;;
esac
