all :
	mkdir -p dest
	cp -pr modules LICENSE readme.txt dob-field-for-cf7.php dest

clean:
	rm -fr dest
