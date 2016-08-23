#!/bin/bash

DIR="languages"
DOMAIN="wpclef"
POT="$DIR/$DOMAIN.pot"
SOURCES="*.php"


# Create template
echo "Creating POT"
rm -f $POT
xgettext --copyright-holder="Clef, Inc." \
    --package-name="WPClef" \
    --package-version="2.6.1" \
    --msgid-bugs-address="support@getclef.com" \
    --language=PHP \
    --sort-output \
    --keyword=__ \
    --keyword=_e \
    --from-code=UTF-8 \
    --output=$POT \
    --default-domain=$DOMAIN \
    `find . -type f -name "*.php" | grep -v ./node_modules | grep -v ./build`

# Update language .po files
for FILE in languages/*.po
do
    LANG=${FILE#languages\/clef\-}
    LANG=${LANG%\.po}

    echo "Updating language file for $LANG from $POT"
    msgmerge --sort-output --update --backup=off $FILE $POT
done

# Sync with Transifex
tx push -s -t
tx pull -af

# Compile language .po files to .mo
for FILE in languages/*.po
do
    LANG=${FILE#languages\/clef\-}
    LANG=${LANG%\.po}

    echo "Compiling $LANG.po to $LANG.mo"
    msgfmt --check --verbose --output-file=languages/clef-$LANG.mo $FILE
done



