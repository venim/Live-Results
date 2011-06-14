#!/usr/local/bin/python

print "Content-type: text/html"
import cgi, os, xlrd, re

def uni2html(u):
	from htmlentitydefs import codepoint2name 
	l=[]
	for c in u:
		if ord(c) < 128:
			l.append(c)
		else:
			l.append('&%s;' % codepoint2name[ord(c)])
	return ''.join(l)
	
form=cgi.FieldStorage()
if not form:
	print """
	<html>
	<body>
	<form action="./" method="post" enctype="multipart/form-data">
	<input type="file" name="filename"><br>
	<input type="submit">
	</form>
	"""
elif form.has_key("filename"):
	item=form['filename']
	match=re.compile('\.xls$').search(item.filename)
	if match:
		open('upload/'+item.filename, 'wb').write(item.file.read())
		book=xlrd.open_workbook('upload/'+item.filename)
		open('upload/dump.sql','w')
		try:
			for sheet_index in range(1, book.nsheets):
				sh=book.sheet_by_index(sheet_index)
				for row in range(4, sh.nrows):
					command=sh.cell_value(rowx=row, colx=sh.ncols-1)
					open('upload/dump.sql','a').write(uni2html(command)+'\n')

		except:
			pass
		os.system('mysql -u  -p -D  < upload/dump.sql')
		s='Success!'
#		try:
#			os.remove('upload/'+item.filename)
#		except:
#			pass
	else:
		s='Sorry, wrong filetype'
	print """
	<html>
	<body>
	"""
	print s
	print """
	<br><br>
	<form action="./" method="post" enctype="multipart/form-data">
	<input type="file" name="filename"><br>
	<input type="submit">
	</form>
	"""