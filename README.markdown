I used PHP to write a program which takes in the input of a plaintext data file and a search string. The program returns both a count of the times the string appears in the file and the average number of words between each instance of the search string.

Below I describe the runtime of my program run in a machine-agnostic manner:

First, the program grabs the phrase the user typed in, whether the user wants the search 
to be case insensitive or not and the file the user wants searched. 

Then the program goes through each line of the file, grab the words of the line and 
compares each word to the phrase the user typed in. 

if it's not a match, the program adds one to a not found counter.
If it's a match then the program adds one to a found counter and 
the program stores the not found counter value. 

At the end, the program displays the found counter value.
The program also displays the average of all the not found counter values, 
except for the first one because the first one isn't 
the number of words between instances of the phrase the user typed in.