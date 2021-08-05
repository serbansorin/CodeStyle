#!/bin/bash

############################################################
# Help                                                     #
############################################################
Help()
{
   # Display Help
   echo "PhpUnit Testing Description"
   echo
   echo "Syntax: scriptTemplate [-t|-f]"
   echo "options:"
   echo "f     First Argument Test || 2nd Class Name"
   echo "Records name of the file and the second run doesn't require -f OPTION"
   echo "Just run #>test [TestNameFromClass]"
   echo "h     Print this Help."

}

############################################################
############################################################
# Main program                                             #
############################################################
############################################################
# Get the options
if [ ! -f "./tmp.dat" ] ; then
  touch ./tmp.dat
  : > tmp.dat 

# otherwise read the value from the file
else
  value=$(head tmp.dat)
fi


while getopts "hf:t:" option; do
   case $option in
        h)
         # display Help
         shift
         Help
         exit;;
        #Normal Testing;
        f) 
        shift
        if test $# -gt 0; then
        export FILE=$1
        export TEST=$2
        echo "$FILE > ./tmp.dat"
        php vendor/bin/phpunit --filter $TEST tests/$FILE.php
        shift
        exit 0
        else
        echo "no Test specified"
        Help
        exit 1
        fi
        shift
        ;;

        #phpUnit Test entire File with 512MB memory
        t)
        shift
        if test $# -gt 0; then
        
        FILE=${OPTARG}
        echo $FILE > tmp.dat
        ./vendor/bin/phpunit -d memory_limit=512M -c phpunit.xml tests/$FILE.php
        echo "Test Done using 512MB memory limit"
        exit 0
        shift
        
        else
        echo "no Test specified"
        Help
        exit 1
        fi
        shift
        ;;
        *) # Invalid option
         echo "Error: Invalid option"
         Help
         exit;;
   esac
done


#TODO ADD MORE OPTIONS, TODO: SAVE FILENAME ENTER ONLY ONCE
export FILE1=$value

if test $# -gt 0; then
   if test ["--clear" = "$1"] ;then
   : > ./tmp.dat
   echo "'./tmp.dat' file was cleared"
   else
   export TEST1=$1
   php vendor/bin/phpunit --filter $TEST1 tests/$FILE1.php
   fi
else #if run script empty, run last known test file
Help
FILE=$value
./vendor/bin/phpunit -d memory_limit=512M -c phpunit.xml tests/$FILE.php
fi
