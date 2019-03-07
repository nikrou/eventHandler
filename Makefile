DIST=.dist
PLUGIN_NAME=$(shell basename `pwd`)
VERSION=$(shell grep Version _define.php | cut -d"'" -f2)
TARGET=../target

config: clean manifest
	mkdir -p $(DIST)/$(PLUGIN_NAME)
	cp -pr _*.php BUGS CHANGELOG.md CONTRIBUTING.md LICENSE MANIFEST CHANGELOG.md TODO.md README.md \
	css default-templates exemple-templates inc js locales tpl \
	icon-b.png inco.png index.php $(DIST)/$(PLUGIN_NAME)/; \
	find $(DIST) -name '*~' -exec rm \{\} \;


dist: config
	cd $(DIST); \
	mkdir -p $(TARGET); \
	zip -v -r9 $(TARGET)/plugin-$(PLUGIN_NAME)-$(VERSION).zip $(PLUGIN_NAME)/*; \

manifest:
	@find ./ -type f|egrep -v '(*~|.git|.gitignore|.dist|vendor|target|modele|Makefile|rsync_exclude)'|sed -e 's/\.\///' -e 's/\(.*\)/$(PLUGIN_NAME)\/&/'> ./MANIFEST

clean:
	rm -fr $(DIST)
