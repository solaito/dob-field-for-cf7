all :
	mkdir -p dest
	cp -pr modules LICENSE readme.txt watts.php dest

clean:
	rm -fr dest
