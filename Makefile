all :
	mkdir -p dest
	cp -pr includes modules LICENSE readme.txt watts.php uninstall.php dest
	yarn run build

clean:
	rm -fr dest
