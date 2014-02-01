import math

# Python script to generate itinerary (:

# inputs: destination, arrive_date, leave_date, budget
# data we already have from: Yelp, Google Maps, Foursquare APIs

# hours of operation for places
# list top 5 potential itineraries that could be compatible for the user

# Input specifications: arrival: date of arrival in MM/DD/YYYY;
# leaving: date of leaving in MM/DD/YYYY; budget: just budget;
# tags: list of IDs of categories, separated by comma (e.g. 1,13,14)

# [{"id":1,"name":"Metropolitan Museum","start_time":"01\/02\/2014 07:00:00",
# "end_time":"01\/02\/2014 12:00:00","address":"New York City, NY"},
# {"id":2,"name":"Peiking Duck Restaurant","start_time":"01\/02\/2014 12:30:00",
# "end_time":"01\/02\/2014 13:30:00","address":"The Empire State Building"}]

def main():
    places = []
    itineraries = []
    for i in xrange(numberOfCategories):
        # generate top 10 places from category in the given destination 
        places += [category[i][:10]]
    # pick 3 meal locations, and 3 activity locations based on budget
    meals = ["meals1", "meals2", "meals3"] # sorry don't mind the crappy style of this very pseudo pseudocode
    activities = ["activities1", "activities2", "activities3"] 
    # budget total can end up having a +/- 10% price margin
    # now to figure out what order and what times everything should happen
    # in the itinerary
    # standard order? meal, activity, meal, activity, meal, activity
    # permute three meals based on what times they're available
    # permute three activities based on what times they're available
    return itineraries
