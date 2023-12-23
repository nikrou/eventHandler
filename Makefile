DIST=.dist
PLUGIN_NAME=$(shell basename `pwd`)
VERSION=$(shell grep Version _define.php | cut -d"'" -f2)
TARGET=../target

config: clean manifest
	mkdir -p $(DIST)/$(PLUGIN_NAME)
	cp -pr _define.php BUGS CHANGELOG.md CONTRIBUTING.md LICENSE MANIFEST TODO.md README.md \
	src css default-templates exemple-templates js locales tpl \
	icon-dark.svg icon.svg $(DIST)/$(PLUGIN_NAME)/
	find $(DIST) -name '*~' -exec rm \{\} \;

dist: config
	cd $(DIST); \
	mkdir -p $(TARGET); \
	rm -f $(TARGET)/plugin-$(PLUGIN_NAME)-$(VERSION).zip; \
	zip -v -r9 $(TARGET)/plugin-$(PLUGIN_NAME)-$(VERSION).zip $(PLUGIN_NAME); \
	cd ..

manifest:
	@find ./ -type f|egrep -v '(*~|.git|.gitignore|.dist|target|bin|composer.*|phpstan.*|.vscode|.php-cs-fixer.cache|vendor|rector.php|deploy.mk|Makefile|rsync_exclude)'|sed -e 's/\.\///' -e 's/\(.*\)/$(PLUGIN_NAME)\/&/'> ./MANIFEST

clean:
	rm -fr $(DIST)

##
XGETTEXT=/usr/bin/xgettext
XGETTEXT_PHP=$(XGETTEXT) -k__ -j -L PHP --from-code=utf-8 -o locales/templates/messages.pot
XGETTEXT_TEMPLATES=$(XGETTEXT) -f- --sort-by-file -L PHP -k"__:1,2" -k"__:1" --no-wrap --foreign-user --from-code=utf-8 -o locales/templates/messages.pot
GETTEXT_FORMAT=/usr/bin/msgfmt
GETTEXT_MERGE=/usr/bin/msgmerge

SEARCH_PATTERN=(.php|.tpl)$
EXCLUDE_PATTERN=(vendor|target|.dist)
DUMMY_FILE=./__html_tpl_dummy.php

search: $(DUMMY_FILE)
	find ./ -type f|egrep '$(SEARCH_PATTERN)'|egrep -v '$(EXCLUDE_PATTERN)'|while read f;do $(XGETTEXT_PHP) $$f;done
	rm $(DUMMY_FILE)

merge:
	@for l in locales/*/*.po;							\
	do										\
	$(GETTEXT_MERGE) $$l locales/templates/messages.pot --output=$$l	;	\
	done

$(DUMMY_FILE): templates

templates:
	echo "Building public PO template..."
	@echo '<?php' > $(DUMMY_FILE)
	@find ./ -name '*.html' -exec grep -o '{{tpl:lang [^}]*}}' {} \; | sed 's/{{tpl:lang \(.*\)$\}}/__("\1")/' | sort -u >> $(DUMMY_FILE)
	@find . -name '__html_tpl_dummy.php' -print | $(XGETTEXT_TEMPLATES)





